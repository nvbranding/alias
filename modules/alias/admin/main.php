<?php
/**
 * @Project NUKEVIET 4.x
 * @Author NV SYSTEMS  (hoangnt@nguyenvan.vn)
 * @Copyright (C) 2014 NV SYSTEMS . All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 2-10-2010 20:59
 */

if ( ! defined( 'NV_IS_FILE_ADMIN' ) ) die( 'Stop!!!' );



if( $nv_Request->isset_request( 'update_alias', 'post, get' ) )
{
	$update_alias = $nv_Request->get_int( 'update_alias','get', 0 );
	if($update_alias > 0)
	{
		// XỬ LÝ ĐỒNG BỘ ALIAS - XÓA
		$list_alias = $db->query('SELECT module FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows GROUP BY module')->fetchAll();
		foreach($list_alias as $module_alias)
		{
			if (!isset($site_mods[$module_alias['module']]))
			{
				
				$query_delete = $db->query("DELETE FROM " . NV_PREFIXLANG . "_" . $module_data . "_rows WHERE module like '". $module_alias['module'] ."' ");
				
			}
		}
		
		// KẾT THÚC XỬ LÝ XÓA
		
		
		die(ok);
	
	}
	
}


if( $nv_Request->isset_request( 'mod_file', 'get' ) )
{
	$mod_file = $nv_Request->get_title( 'mod_file', 'get', '' );
	$mod_data = $nv_Request->get_title( 'mod_data', 'get', '' );
	$mod_name = $nv_Request->get_title( 'mod_name', 'get', '' );
	
	if(!empty($mod_file) and !empty($mod_data) and !empty($mod_name))
	{
		$check_alias = new NukeViet\Alias\Checkalias;
		
		$mod_alias = $db->query('SELECT alias FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows')->fetchAll();
		
		// TRƯỜNG HỢP LÀ SHOPS
		if($mod_file == 'shops')
		{
			// Cập nhật alias danh mục 
			
			$list_danhmuc = $db->query('SELECT '. NV_LANG_DATA . '_alias as alias, catid FROM ' . $db_config['prefix'] . '_' . $mod_data . '_catalogs')->fetchAll();
			foreach($list_danhmuc as $data)
			{
				
				// Thêm id_alias danh mục
				if(!in_array($data['alias'],$mod_alias))
				{
					$check_alias->add_alias_catalogy_news($data['catid'], $data['alias'], $mod_name, 'viewcat');
				}
			}
			
			// Cập nhật chi tiết bài viết
			
			$list_detail = $db->query('SELECT id, listcatid, '. NV_LANG_DATA . '_alias as alias FROM ' . $db_config['prefix'] . '_' . $mod_data . '_rows')->fetchAll();
			foreach($list_detail as $data)
			{
				
				// Thêm id_alias chi tiết
				if(!in_array($data['alias'],$mod_alias))
				{
					$check_alias->add_alias_news($data['listcatid'],$data['id'], $data['alias'], $mod_name, 'detail');
				}
			}
			
			
			// XÓA ALIAS CHUYÊN MỤC
			$alias_danhmuc = $db->query('SELECT alias, id FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE module ="'. $mod_name .'" AND op="viewcat"')->fetchAll();
			
			$sql = 'SELECT '. NV_LANG_DATA . '_alias as alias FROM ' . $db_config['prefix'] . '_' . $mod_data . '_catalogs';
			$nv_Cache->delMod( $mod_name );
			$danhmuc = $nv_Cache->db($sql, 'alias', $mod_name);
		
			foreach($alias_danhmuc as $data)
			{
				if (!isset($danhmuc[$data['alias']]))
				{
					// XÓA ALIAS 
					$db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows  WHERE id = ' . $db->quote( $data['id'] ) );
				}
			}
			
			// XÓA ALIAS CHI TIẾT
			$alias_chitiet = $db->query('SELECT alias, id FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE module ="'. $mod_name .'" AND op="detail"')->fetchAll();
			
			$sql = 'SELECT '. NV_LANG_DATA . '_alias as alias FROM ' . $db_config['prefix'] . '_' . $mod_data . '_rows';
			$nv_Cache->delMod( $mod_name );
			$detail = $nv_Cache->db($sql, 'alias', $mod_name);
			
			foreach($alias_chitiet as $data)
			{
				if (!isset($detail[$data['alias']]))
				{
					// XÓA ALIAS 
					
					$db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows  WHERE id = ' . $db->quote( $data['id'] ) );
				}
			}
			
			// KẾT THÚC XÓA ALIAS
			//begin site 
			$file_module = NV_ROOTDIR . '/modules/' . $mod_file . '/funcs/main.php';
			$file_module_content = @file_get_contents($file_module);
			$file_module_string_find = "'link_pro' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$alias,";
			$file_module_string_remplace = "'link_pro' => NV_BASE_SITEURL . \$alias;//'link_pro' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$alias,";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			$file_module_string_find = "\$base_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name;";
			$file_module_string_remplace = "\$base_url = NV_BASE_SITEURL . \$module_name;//\$base_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name;";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			if (! empty($file_module) and ! empty($file_module_content)) {
				try {
					$filesize = file_put_contents($file_module, $file_module_content, LOCK_EX);
					if (empty($filesize)) {
						$return = false;
					}
				} catch (exception $e) {
					$return = false;
				}
			}
			
			//end site
			//begin admin site
			$file_module = NV_ROOTDIR . '/modules/' . $mod_file . '/admin/main.php';
			$file_module_content = @file_get_contents($file_module);
			$file_module_string_find = "'link' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=' . \$global_array_cat[\$catid_i]['alias'] . '/' . \$alias . '-' . \$id . \$global_config['rewrite_exturl'],";
			$file_module_string_remplace = "'link' => nv_url_rewrite(NV_BASE_SITEURL . \$alias . \$global_config['rewrite_exturl'], true),//\'link' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=' . \$global_array_cat[\$catid_i]['alias'] . '/' . \$alias . '-' . \$id . \$global_config['rewrite_exturl'],";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			if (! empty($file_module) and ! empty($file_module_content)) {
				try {
					$filesize = file_put_contents($file_module, $file_module_content, LOCK_EX);
					if (empty($filesize)) {
						$return = false;
					}
				} catch (exception $e) {
					$return = false;
				}
			}
			//end admin site
			
		}
		
		// TRƯỜNG HỢP LÀ NEWS
		if($mod_file == 'news')
		{
			// Cập nhật alias danh mục 
			//print(ass);die;
			$list_danhmuc = $db->query('SELECT alias, catid FROM ' . NV_PREFIXLANG . '_' . $mod_data . '_cat')->fetchAll();
			foreach($list_danhmuc as $data)
			{
				
				// Thêm id_alias danh mục
				if(!in_array($data['alias'],$mod_alias))
				{
					$check_alias->add_alias_catalogy_news($data['catid'], $data['alias'], $mod_name, $data['alias']);
				}
			}
			
			// Cập nhật chi tiết bài viết
			
			$list_detail = $db->query('SELECT id, catid, alias FROM ' . NV_PREFIXLANG . '_' . $mod_data . '_rows')->fetchAll();
			foreach($list_detail as $data)
			{
				
				// Thêm id_alias chi tiết
				if(!in_array($data['alias'],$mod_alias))
				{
					$check_alias->add_alias_news($data['catid'],$data['id'], $data['alias'], $mod_name, 'detail');
				}
			}
			
			
			// XÓA ALIAS CHUYÊN MỤC
			$alias_danhmuc = $db->query('SELECT alias, id FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE module ="'. $mod_name .'" AND op="viewcat"')->fetchAll();
			
			$sql = 'SELECT alias FROM ' . NV_PREFIXLANG . '_' . $mod_data . '_cat';
			$nv_Cache->delMod( $mod_name );
			$danhmuc = $nv_Cache->db($sql, 'alias', $mod_name);
		
			foreach($alias_danhmuc as $data)
			{
				if (!isset($danhmuc[$data['alias']]))
				{
					// XÓA ALIAS 
					$db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows  WHERE id = ' . $db->quote( $data['id'] ) );
				}
			}
			
			// XÓA ALIAS CHI TIẾT
			$alias_chitiet = $db->query('SELECT alias, id FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE module ="'. $mod_name .'" AND op="detail"')->fetchAll();
			
			$sql = 'SELECT alias FROM ' . NV_PREFIXLANG . '_' . $mod_data . '_rows';
			$nv_Cache->delMod( $mod_name );
			$detail = $nv_Cache->db($sql, 'alias', $mod_name);
			
			foreach($alias_chitiet as $data)
			{
				if (!isset($detail[$data['alias']]))
				{
					// XÓA ALIAS 
					
					$db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows  WHERE id = ' . $db->quote( $data['id'] ) );
				}
			}
			
			// KẾT THÚC XÓA ALIAS
			//begin site 
			$file_module = NV_ROOTDIR . '/modules/' . $mod_file . '/funcs/main.php';
			$file_module_content = @file_get_contents($file_module);
			$file_module_string_find = "\$base_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name;";
			$file_module_string_remplace = "\$base_url = NV_BASE_SITEURL . \$module_name;//\$base_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name;";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			$file_module_content = @file_get_contents($file_module);
			$file_module_string_find = "\$item['link'] = nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '='.\$item['alias'],true);";
			$file_module_string_remplace = "\$item['link'] = nv_url_rewrite(NV_BASE_SITEURL . \$item['alias'],true);//\$item['link'] = nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '='.\$item['alias'],true);";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			$file_module_string_find = "\$_other['link'] = \$base_url . '&amp;' . NV_OP_VARIABLE . '=' . \$_other['alias'] . \$global_config['rewrite_exturl'];";
			$file_module_string_remplace = "\$_other['link'] = nv_url_rewrite(NV_BASE_SITEURL . \$_other['alias'], true);//\$_other['link'] = \$base_url . '&amp;' . NV_OP_VARIABLE . '=' . \$_other['alias'] . \$global_config['rewrite_exturl'];";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			$file_module_string_find = "\$item['link'] = \$array_cat_i['link'] . '/' . \$item['alias'] . '-' . \$item['id'] . \$global_config['rewrite_exturl'];";
			$file_module_string_remplace = "\$item['link'] = \$item['alias'];//\$item['link'] = \$array_cat_i['link'] . '/' . \$item['alias'] . '-' . \$item['id'] . \$global_config['rewrite_exturl'];";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			
			$file_module_string_find = "\$item['link'] = \$global_array_cat[\$item['catid']]['link'] . '/' . \$item['alias'] . '-' . \$item['id'] . \$global_config['rewrite_exturl'];";
			$file_module_string_remplace = "\$item['link'] = \$item['alias'];//\$item['link'] = \$global_array_cat[\$item['catid']]['link'] . '/' . \$item['alias'] . '-' . \$item['id'] . \$global_config['rewrite_exturl'];";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			if (! empty($file_module) and ! empty($file_module_content)) {
				try {
					$filesize = file_put_contents($file_module, $file_module_content, LOCK_EX);
					if (empty($filesize)) {
						$return = false;
					}
				} catch (exception $e) {
					$return = false;
				}
			}
			
			$file_module = NV_ROOTDIR . '/modules/' . $mod_file . '/funcs/viewcat.php';
			$file_module_content = @file_get_contents($file_module);
			$file_module_string_find = "\$base_url_rewrite = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . \$module_name . '&' . NV_OP_VARIABLE . '=' . \$global_array_cat[\$catid]['alias'];";
			$file_module_string_remplace = "\$base_url_rewrite = NV_BASE_SITEURL . \$global_array_cat[\$catid]['alias'];//\$base_url_rewrite = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . \$module_name . '&' . NV_OP_VARIABLE . '=' . \$global_array_cat[\$catid]['alias'];";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			$file_module_string_find = "\$item['link'] = \$global_array_cat[\$catid_i]['link'] . '/' . \$item['alias'] . '-' . \$item['id'] . \$global_config['rewrite_exturl'];";
			$file_module_string_remplace = "\$item['link'] = \$item['alias'];//\$item['link'] = \$global_array_cat[\$catid_i]['link'] . '/' . \$item['alias'] . '-' . \$item['id'] . \$global_config['rewrite_exturl'];";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			$file_module_string_find = "\$item['link'] = \$global_array_cat[\$catid]['rewrite_link'] . '/' . \$item['alias'] . \$global_config['rewrite_exturl'];";
			$file_module_string_remplace = "\$item['link'] = \$item['alias'];//\$item['link'] = \$global_array_cat[\$catid]['rewrite_link'] . '/' . \$item['alias'] . \$global_config['rewrite_exturl'];";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			$file_module_string_find = "\$item['link'] = \$global_array_cat[\$catid]['link'] . '/' . \$item['alias'] . '-' . \$item['id'] . \$global_config['rewrite_exturl'];";
			$file_module_string_remplace = "\$item['link'] = \$item['alias'];//\$item['link'] = \$global_array_cat[\$catid]['link'] . '/' . \$item['alias'] . '-' . \$item['id'] . \$global_config['rewrite_exturl'];";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			$file_module_string_find = "\$_other['link'] = \$base_url . '&amp;' . NV_OP_VARIABLE . '=' . \$_other['alias'] . \$global_config['rewrite_exturl'];";
			$file_module_string_remplace = "\$_other['link'] = nv_url_rewrite(NV_BASE_SITEURL . \$_other['alias'], true);//\$_other['link'] = \$base_url . '&amp;' . NV_OP_VARIABLE . '=' . \$_other['alias'] . \$global_config['rewrite_exturl'];";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			if (! empty($file_module) and ! empty($file_module_content)) {
				try {
					$filesize = file_put_contents($file_module, $file_module_content, LOCK_EX);
					if (empty($filesize)) {
						$return = false;
					}
				} catch (exception $e) {
					$return = false;
				}
			}
			$file_module = NV_ROOTDIR . '/modules/' . $mod_file . '/functions.php';
			$file_module_content = @file_get_contents($file_module);
			$file_module_string_find = "\$global_array_cat[\$l['catid']]['link'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=' . \$l['alias'];";
			$file_module_string_remplace = "\$global_array_cat[\$l['catid']]['link'] = NV_BASE_SITEURL . \$l['alias'];//\$global_array_cat[\$l['catid']]['link'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=' . \$l['alias'];";
			if(strpos($file_module_content,$file_module_string_remplace) != true){
				if(strpos($file_module_content,$file_module_string_find)){
					$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
				}
				if (! empty($file_module) and ! empty($file_module_content)) {
					try {
						$filesize = file_put_contents($file_module, $file_module_content, LOCK_EX);
						if (empty($filesize)) {
							$return = false;
						}
					} catch (exception $e) {
						$return = false;
					}
				}
			}
			$file_module = NV_ROOTDIR . '/modules/' . $mod_file . '/functions.php';
			$file_module_content = @file_get_contents($file_module);
			$file_module_string_find = "\$catid = 0;";
			$file_module_string_remplace = "if(\$link_true){ \$catid = 0; }";
			if(strpos($file_module_content,$file_module_string_remplace) != true){
				if(strpos($file_module_content,$file_module_string_find)){
					$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
				}
				if (! empty($file_module) and ! empty($file_module_content)) {
					try {
						$filesize = file_put_contents($file_module, $file_module_content, LOCK_EX);
						if (empty($filesize)) {
							$return = false;
						}
					} catch (exception $e) {
						$return = false;
					}
				}
			}
			$file_module = NV_ROOTDIR . '/modules/' . $mod_file . '/functions.php';
			$file_module_content = @file_get_contents($file_module);
			$file_module_string_find = "\$id = intval(end(\$array_page));";
			$file_module_string_remplace = "//\$id = intval(end(\$array_page));";
			if(strpos($file_module_content,$file_module_string_remplace) != true){
				if(strpos($file_module_content,$file_module_string_find)){
					$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
				}
				if (! empty($file_module) and ! empty($file_module_content)) {
					try {
						$filesize = file_put_contents($file_module, $file_module_content, LOCK_EX);
						if (empty($filesize)) {
							$return = false;
						}
					} catch (exception $e) {
						$return = false;
					}
				}
			}
			
			
			$file_module = NV_ROOTDIR . '/modules/' . $mod_file . '/funcs/detail.php';
			$file_module_content = @file_get_contents($file_module);
			$file_module_string_find = "\$base_url_rewrite = nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . \$module_name . '&' . NV_OP_VARIABLE . '=' . \$global_array_cat[\$news_contents['catid']]['alias'] . '/' . \$news_contents['alias'] . '-' . \$news_contents['id'] . \$global_config['rewrite_exturl'], true);";
			$file_module_string_remplace = "\$base_url_rewrite = nv_url_rewrite(NV_BASE_SITEURL . \$news_contents['alias'] . \$global_config['rewrite_exturl'], true);//\$base_url_rewrite = nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . \$module_name . '&' . NV_OP_VARIABLE . '=' . \$global_array_cat[\$news_contents['catid']]['alias'] . '/' . \$news_contents['alias'] . '-' . \$news_contents['id'] . \$global_config['rewrite_exturl'], true);";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			$file_module_string_find = "\$link = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=' . \$global_array_cat[\$catid]['alias'] . '/' . \$row['alias'] . '-' . \$row['id'] . \$global_config['rewrite_exturl'];";
			$file_module_string_remplace = "\$link = NV_BASE_SITEURL . \$row['alias'] . \$global_config['rewrite_exturl'];//\$link = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=' . \$global_array_cat[\$catid]['alias'] . '/' . \$row['alias'] . '-' . \$row['id'] . \$global_config['rewrite_exturl'];";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			$file_module_string_find = "\$link = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=' . \$global_array_cat[\$row['catid']]['alias'] . '/' . \$row['alias'] . '-' . \$row['id'] .\$global_config['rewrite_exturl'];";
			$file_module_string_remplace = "\$link = NV_BASE_SITEURL . \$row['alias'] . \$global_config['rewrite_exturl'];//\$link = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=' . \$global_array_cat[\$row['catid']]['alias'] . '/' . \$row['alias'] . '-' . \$row['id'] .\$global_config['rewrite_exturl'];";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			$file_module_string_find = "\$topiclink = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=' . \$module_info['alias']['topic'] . '/' . \$topic_alias;";
			$file_module_string_remplace = "\$topiclink = NV_BASE_SITEURL . \$topic_alias . \$global_config['rewrite_exturl'];//\$topiclink = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=' . \$module_info['alias']['topic'] . '/' . \$topic_alias;";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			
			if (! empty($file_module) and ! empty($file_module_content)) {
				try {
					$filesize = file_put_contents($file_module, $file_module_content, LOCK_EX);
					if (empty($filesize)) {
						$return = false;
					}
				} catch (exception $e) {
					$return = false;
				}
			}
			$file_module = NV_ROOTDIR . '/modules/' . $mod_file . '/funcs/sitemap.php';
			$file_module_content = @file_get_contents($file_module);
			$file_module_string_find = "'link' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=' . \$catalias . '/' . \$alias . '-' . \$id . \$global_config['rewrite_exturl'],";
			$file_module_string_remplace = "'link' => nv_url_rewrite(NV_BASE_SITEURL . \$alias . \$global_config['rewrite_exturl'], true),//'link' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=' . \$catalias . '/' . \$alias . '-' . \$id . \$global_config['rewrite_exturl'],";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			
			if (! empty($file_module) and ! empty($file_module_content)) {
				try {
					$filesize = file_put_contents($file_module, $file_module_content, LOCK_EX);
					if (empty($filesize)) {
						$return = false;
					}
				} catch (exception $e) {
					$return = false;
				}
			}
			
			$file_module = NV_ROOTDIR . '/modules/' . $mod_file . '/rssdata.php';
			$file_module_content = @file_get_contents($file_module);
			$file_module_string_find = "\$value['link'] = NV_BASE_SITEURL . \"index.php?\" . NV_LANG_VARIABLE . \"=\" . NV_LANG_DATA . \"&amp;\" . NV_NAME_VARIABLE . \"=\" . \$mod_name . \"&amp;\" . NV_OP_VARIABLE . \"=\" . \$mod_info['alias']['rss'] . \"\/\" . \$value['alias'];";
			$file_module_string_remplace = "\$value['link'] = nv_url_rewrite(NV_BASE_SITEURL . \$value['alias'] . \$global_config['rewrite_exturl'], true);//\$value['link'] = NV_BASE_SITEURL . \"index.php?\" . NV_LANG_VARIABLE . \"=\" . NV_LANG_DATA . \"&amp;\" . NV_NAME_VARIABLE . \"=\" . \$mod_name . \"&amp;\" . NV_OP_VARIABLE . \"=\" . \$mod_info['alias']['rss'] . \"\/\" . \$value['alias'];";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			
			if (! empty($file_module) and ! empty($file_module_content)) {
				try {
					$filesize = file_put_contents($file_module, $file_module_content, LOCK_EX);
					if (empty($filesize)) {
						$return = false;
					}
				} catch (exception $e) {
					$return = false;
				}
			}
			$file_module = NV_ROOTDIR . '/modules/' . $mod_file . '/theme.php';
			$file_module_content = @file_get_contents($file_module);
			$file_module_string_find = "\$xtpl->assign('LINK', \$global_array_cat[\$catid_i]['link'] . '/' . \$value['alias'] . "-" . \$value['id'] . \$global_config['rewrite_exturl']);";
			$file_module_string_remplace = "\$xtpl->assign('LINK',\$value['alias']);//\$xtpl->assign('LINK', \$global_array_cat[\$catid_i]['link'] . '/' . \$value['alias'] . "-" . \$value['id'] . \$global_config['rewrite_exturl']);";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			$file_module_string_find = "\$xtpl->assign('LINK_KEYWORDS', NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=tag/' . urlencode(\$value['alias']));";
			$file_module_string_remplace = "\$xtpl->assign('LINK_KEYWORDS', NV_BASE_SITEURL . urlencode(\$value['alias']));//\$xtpl->assign('LINK_KEYWORDS', NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=tag/' . urlencode(\$value['alias']));";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			
			if (! empty($file_module) and ! empty($file_module_content)) {
				try {
					$filesize = file_put_contents($file_module, $file_module_content, LOCK_EX);
					if (empty($filesize)) {
						$return = false;
					}
				} catch (exception $e) {
					$return = false;
				}
			}
			$file_module = NV_ROOTDIR . '/modules/' . $mod_file . '/blocks/module.block_newscenter.php';
			$file_module_content = @file_get_contents($file_module);
			$file_module_string_find = "\$row['link'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=' . \$global_array_cat[\$row['catid']]['alias'] . '/' . \$row['alias'] . '-' . \$row['id'] . \$global_config['rewrite_exturl'];";
			$file_module_string_remplace = "\$row['link'] = \$row['alias'] . \$global_config['rewrite_exturl'];//\$row['link'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=' . \$global_array_cat[\$row['catid']]['alias'] . '/' . \$row['alias'] . '-' . \$row['id'] . \$global_config['rewrite_exturl'];";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			$file_module_string_find = "\$xtpl->assign('LINK_KEYWORDS', NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=tag/' . urlencode(\$value['alias']));";
			$file_module_string_remplace = "\$xtpl->assign('LINK_KEYWORDS', NV_BASE_SITEURL . urlencode(\$value['alias']));//\$xtpl->assign('LINK_KEYWORDS', NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=tag/' . urlencode(\$value['alias']));";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			
			if (! empty($file_module) and ! empty($file_module_content)) {
				try {
					$filesize = file_put_contents($file_module, $file_module_content, LOCK_EX);
					if (empty($filesize)) {
						$return = false;
					}
				} catch (exception $e) {
					$return = false;
				}
			}
			//end site
			//begin admin site
			$file_module = NV_ROOTDIR . '/modules/' . $mod_file . '/admin/main.php';
			$file_module_content = @file_get_contents($file_module);
			$file_module_string_find = "'link' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=' . \$global_array_cat[\$catid_i]['alias'] . '/' . \$alias . '-' . \$id . \$global_config['rewrite_exturl'],";
			$file_module_string_remplace = "'link' => nv_url_rewrite(NV_BASE_SITEURL . \$alias . \$global_config['rewrite_exturl'], true),//\'link' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=' . \$global_array_cat[\$catid_i]['alias'] . '/' . \$alias . '-' . \$id . \$global_config['rewrite_exturl'],";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			if (! empty($file_module) and ! empty($file_module_content)) {
				try {
					$filesize = file_put_contents($file_module, $file_module_content, LOCK_EX);
					if (empty($filesize)) {
						$return = false;
					}
				} catch (exception $e) {
					$return = false;
				}
			}
			//end admin site
			
			
		}
		
		// TRƯỜNG HỢP LÀ PAGE
		if($mod_file == 'page' or $mod_file == 'about' or $mod_file == 'alias')
		{
			
		
			// Cập nhật chi tiết bài viết
			
			$list_detail = $db->query('SELECT id, alias FROM ' . NV_PREFIXLANG . '_' . $mod_data)->fetchAll();
			foreach($list_detail as $data)
			{
				
				// Thêm id_alias chi tiết
				if(!in_array($data['alias'],$mod_alias))
				{
					$check_alias->add_alias_page($data['id'], $data['alias'], $mod_name, 'main');
				}
			}
			
			// XÓA ALIAS CHI TIẾT
			$alias_chitiet = $db->query('SELECT alias, id FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE module ="'. $mod_name .'" AND op="main"')->fetchAll();
			
			$sql = 'SELECT alias FROM ' . NV_PREFIXLANG . '_' . $mod_data;
			$nv_Cache->delMod( $mod_name );
			$detail = $nv_Cache->db($sql, 'alias', $mod_name);
			
			foreach($alias_chitiet as $data)
			{
				if (!isset($detail[$data['alias']]))
				{
					// XÓA ALIAS 
					
					$db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows  WHERE id = ' . $db->quote( $data['id'] ) );
				}
			}
			
			// KẾT THÚC XÓA ALIAS
			//begin site
			$file_module = NV_ROOTDIR . '/modules/' . $mod_file . '/funcs/main.php';
			$file_module_content = @file_get_contents($file_module);
			$file_module_string_find = "\$base_url_rewrite = nv_url_rewrite(str_replace('&amp;', '&', \$base_url) . '&' . NV_OP_VARIABLE . '=' . \$rowdetail['alias'] . \$global_config['rewrite_exturl'], true);";
			$file_module_string_remplace = "\$base_url_rewrite = nv_url_rewrite(NV_BASE_SITEURL . \$rowdetail['alias'] . \$global_config['rewrite_exturl'], true);//\$base_url_rewrite = nv_url_rewrite(str_replace('&amp;', '&', \$base_url) . '&' . NV_OP_VARIABLE . '=' . \$rowdetail['alias'] . \$global_config['rewrite_exturl'], true);";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			$file_module_string_find = "\$_other['link'] = \$base_url . '&amp;' . NV_OP_VARIABLE . '=' . \$_other['alias'] . \$global_config['rewrite_exturl'];";
			$file_module_string_remplace = "\$_other['link'] = nv_url_rewrite(NV_BASE_SITEURL . \$_other['alias'] . \$global_config['rewrite_exturl'], true);//\$_other['link'] = \$base_url . '&amp;' . NV_OP_VARIABLE . '=' . \$_other['alias'] . \$global_config['rewrite_exturl'];";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			if (! empty($file_module) and ! empty($file_module_content)) {
				try {
					$filesize = file_put_contents($file_module, $file_module_content, LOCK_EX);
					if (empty($filesize)) {
						$return = false;
					}
				} catch (exception $e) {
					$return = false;
				}
			}
			$file_module = NV_ROOTDIR . '/modules/' . $mod_file . '/functions.php';
			$file_module_content = @file_get_contents($file_module);
			$file_module_string_find = "\$alias = (!empty(\$array_op) and !empty(\$array_op[0])) ? \$array_op[0] : '';";
			$file_module_string_remplace = "if(\$link_true) \$alias = (!empty(\$array_op) and !empty(\$array_op[0])) ? \$array_op[0] : '';";
			if(strpos($file_module_content,$file_module_string_remplace) != true){
				if(strpos($file_module_content,$file_module_string_find)){
					$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
				}
				if (! empty($file_module) and ! empty($file_module_content)) {
					try {
						$filesize = file_put_contents($file_module, $file_module_content, LOCK_EX);
						if (empty($filesize)) {
							$return = false;
						}
					} catch (exception $e) {
						$return = false;
					}
				}
			}
			$file_module = NV_ROOTDIR . '/modules/' . $mod_file . '/funcs/sitemap.php';
			$file_module_content = @file_get_contents($file_module);
			$file_module_string_find = "'link' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=' . \$catalias . '/' . \$alias . '-' . \$id . \$global_config['rewrite_exturl'],";
			$file_module_string_remplace = "'link' => nv_url_rewrite(NV_BASE_SITEURL . \$alias . \$global_config['rewrite_exturl'], true),//'link' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=' . \$catalias . '/' . \$alias . '-' . \$id . \$global_config['rewrite_exturl'],";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			
			if (! empty($file_module) and ! empty($file_module_content)) {
				try {
					$filesize = file_put_contents($file_module, $file_module_content, LOCK_EX);
					if (empty($filesize)) {
						$return = false;
					}
				} catch (exception $e) {
					$return = false;
				}
			}
			//end site
			//begin admin site
			$file_module = NV_ROOTDIR . '/modules/' . $mod_file . '/admin/main.php';
			$file_module_content = @file_get_contents($file_module);
			$file_module_string_find = "\$row['url_view'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=' . \$row['alias'] . \$global_config['rewrite_exturl'];";
			$file_module_string_remplace = "\$row['url_view'] = nv_url_rewrite(NV_BASE_SITEURL . \$row['alias'] . \$global_config['rewrite_exturl'], true);//\$row['url_view'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . \$module_name . '&amp;' . NV_OP_VARIABLE . '=' . \$row['alias'] . \$global_config['rewrite_exturl'];";
			if(strpos($file_module_content,$file_module_string_find)){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			if (! empty($file_module) and ! empty($file_module_content)) {
				try {
					$filesize = file_put_contents($file_module, $file_module_content, LOCK_EX);
					if (empty($filesize)) {
						$return = false;
					}
				} catch (exception $e) {
					$return = false;
				}
			}
			$file_module = NV_ROOTDIR . '/modules/' . $mod_file . '/admin/content.php';
			$file_module_content = @file_get_contents($file_module);
			$file_module_string_find = "\$groups_list = nv_groups_list();";
			$file_module_string_remplace = "\$groups_list = nv_groups_list();\n \$check_alias = new NukeViet\Alias\Checkalias;";
			if(strpos($file_module_content,'\$check_alias = new NukeViet\TMS\Checkalias;') == 0){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			$file_module_string_find = "\$groups_list = nv_groups_list();";
			$file_module_string_remplace = "\$groups_list = nv_groups_list();\n \$check_alias = new NukeViet\Alias\Checkalias;";
			if(strpos($file_module_content,"\$check_return = \$check_alias->check_id_alias(\$id, \$row['alias']);") == 0){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			$file_module_string_find = "if (empty(\$row['title'])) {";
			$file_module_string_remplace = "if(\$check_alias->check == 1){ \$error = 'alias bị trùng'; }elseif (empty(\$row['title'])) {";
			if(strpos($file_module_content,"if(\$check_alias->check == 1){ \$error = 'alias bị trùng'; }elseif (empty(\$row['title'])) {") == 0){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			$file_module_string_find = "if (\$sth->rowCount()) {";
			$file_module_string_remplace = "if (\$sth->rowCount()) {\n if(\$id == 0) \$id_page = \$db->lastInsertId(); else \$id_page = \$id;\n \$check_alias->add_alias_page(\$id_page, \$row['alias'], \$module_name, 'main');";
			if(strpos($file_module_content,"\$check_alias->add_alias_page(\$id_page, \$row['alias'], \$module_name, 'main');") == 0){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			$file_module_string_find = "\$xtpl->parse('main');";
			$file_module_string_remplace = "\$list_tag_news = \$check_alias->list_tags();\n \$array_tag_new = explode(',',\$row['keywords']);\n foreach(\$list_tag_news as \$tag){\n if(in_array(\$tag['id'],\$array_tag_new)) \$xtpl->assign('selected_tag', 'selected=selected'); else \$xtpl->assign('selected_tag', '');\n \$xtpl->assign('tag', \$tag);\n \$xtpl->parse('main.tag');}\n \$xtpl->parse('main');";
			if(strpos($file_module_content,"\$list_tag_news = \$check_alias->list_tags();") == 0){
				$file_module_content = str_replace($file_module_string_find,$file_module_string_remplace,$file_module_content);
			}
			
			if (! empty($file_module) and ! empty($file_module_content)) {
				try {
					$filesize = file_put_contents($file_module, $file_module_content, LOCK_EX);
					if (empty($filesize)) {
						$return = false;
					}
				} catch (exception $e) {
					$return = false;
				}
			}
			//end admin site
		}
		
		// TRƯỜNG HỢP LÀ DOWNLOAD
		if($mod_file == 'download')
		{
			// Cập nhật alias danh mục 
			//print(ass);die;
			$list_danhmuc = $db->query('SELECT alias, id FROM ' . NV_PREFIXLANG . '_' . $mod_data . '_categories')->fetchAll();
			foreach($list_danhmuc as $data)
			{
				
				// Thêm id_alias danh mục
				if(!in_array($data['alias'],$mod_alias))
				{
					$check_alias->add_alias_catalogy_news($data['id'], $data['alias'], $mod_name, 'viewcat');
				}
			}
		
			// Cập nhật chi tiết bài viết
			
			$list_detail = $db->query('SELECT id, alias, catid FROM ' . NV_PREFIXLANG . '_' . $mod_data)->fetchAll();
			foreach($list_detail as $data)
			{
				
				// Thêm id_alias chi tiết
				if(!in_array($data['alias'],$mod_alias))
				{
					$check_alias->add_alias_news($data['catid'],$data['id'], $data['alias'], $mod_name, 'viewfile');
				}
			}
			
			
			// XÓA ALIAS CHUYÊN MỤC
			$alias_danhmuc = $db->query('SELECT alias, id FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE module ="'. $mod_name .'" AND op="viewcat"')->fetchAll();
			
			$sql = 'SELECT alias FROM ' . NV_PREFIXLANG . '_' . $mod_data . '_categories';
			$nv_Cache->delMod( $mod_name );
			$danhmuc = $nv_Cache->db($sql, 'alias', $mod_name);
		
			foreach($alias_danhmuc as $data)
			{
				if (!isset($danhmuc[$data['alias']]))
				{
					// XÓA ALIAS 
					$db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows  WHERE id = ' . $db->quote( $data['id'] ) );
				}
			}
			
			// XÓA ALIAS CHI TIẾT
			$alias_chitiet = $db->query('SELECT alias, id FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE module ="'. $mod_name .'" AND op="viewfile"')->fetchAll();
			
			$sql = 'SELECT alias FROM ' . NV_PREFIXLANG . '_' . $mod_data;
			$nv_Cache->delMod( $mod_name );
			$detail = $nv_Cache->db($sql, 'alias', $mod_name);
			
			foreach($alias_chitiet as $data)
			{
				if (!isset($detail[$data['alias']]))
				{
					// XÓA ALIAS 
					
					$db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows  WHERE id = ' . $db->quote( $data['id'] ) );
				}
			}
			
			// KẾT THÚC XÓA ALIAS
			
			
			
		}
		
		// TRƯỜNG HỢP LÀ STORE
		if($mod_file == 'store')
		{
			// Cập nhật alias danh mục 
			//print(ass);die;
			$list_danhmuc = $db->query('SELECT alias, id FROM ' . NV_PREFIXLANG . '_' . $mod_data . '_catalogy')->fetchAll();
			foreach($list_danhmuc as $data)
			{
				
				// Thêm id_alias danh mục
				if(!in_array($data['alias'],$mod_alias))
				{
					$check_alias->add_alias_catalogy_news($data['id'], $data['alias'], $mod_name, 'catalogy');
				}
			}
		
			// Cập nhật chi tiết bài viết
			
			$list_detail = $db->query('SELECT id, alias, catalog FROM ' . NV_PREFIXLANG . '_' . $mod_data .'_rows')->fetchAll();
			foreach($list_detail as $data)
			{
				
				// Thêm id_alias chi tiết
				if(!in_array($data['alias'],$mod_alias))
				{
					$check_alias->add_alias_news($data['catalog'],$data['id'], $data['alias'], $mod_name, 'detail');
				}
			}
			
			
			// XÓA ALIAS CHUYÊN MỤC
			$alias_danhmuc = $db->query('SELECT alias, id FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE module ="'. $mod_name .'" AND op="catalogy"')->fetchAll();
			
			$sql = 'SELECT alias FROM ' . NV_PREFIXLANG . '_' . $mod_data . '_catalogy';
			$nv_Cache->delMod( $mod_name );
			$danhmuc = $nv_Cache->db($sql, 'alias', $mod_name);
		
			foreach($alias_danhmuc as $data)
			{
				if (!isset($danhmuc[$data['alias']]))
				{
					// XÓA ALIAS 
					$db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows  WHERE id = ' . $db->quote( $data['id'] ) );
				}
			}
			
			// XÓA ALIAS CHI TIẾT
			$alias_chitiet = $db->query('SELECT alias, id FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE module ="'. $mod_name .'" AND op="detail"')->fetchAll();
			$nv_Cache->delMod( $mod_name );
			$sql = 'SELECT alias FROM ' . NV_PREFIXLANG . '_' . $mod_data .'_rows';
			$detail1 = $nv_Cache->db($sql, 'alias', $mod_name);
			
			foreach($alias_chitiet as $data)
			{
				if (!isset($detail1[$data['alias']]))
				{
					// XÓA ALIAS 
					
					$db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows  WHERE id = ' . $db->quote( $data['id'] ) );
				}
			}
			
			// KẾT THÚC XÓA ALIAS
			
			
		}
		
		print(ok);die;
	
	}
	
	
}

//change status
if( $nv_Request->isset_request( 'change_status', 'post, get' ) )
{
	$id = $nv_Request->get_int( 'id', 'post, get', 0 );
	$content = 'NO_' . $id;

	$query = 'SELECT weight FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE id=' . $id;
	$row = $db->query( $query )->fetch();
	if( isset( $row['weight'] ) )
	{
		$weight = ( $row['weight'] ) ? 0 : 1;
		$query = 'UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_rows SET weight=' . intval( $weight ) . ' WHERE id=' . $id;
		$db->query( $query );
		$content = 'OK_' . $id;
	}
	$nv_Cache->delMod( $module_name );
	include NV_ROOTDIR . '/includes/header.php';
	echo $content;
	include NV_ROOTDIR . '/includes/footer.php';
	exit();
}

if( $nv_Request->isset_request( 'ajax_action', 'post' ) )
{
	$id = $nv_Request->get_int( 'id', 'post', 0 );
	$new_vid = $nv_Request->get_int( 'new_vid', 'post', 0 );
	$content = 'NO_' . $id;
	if( $new_vid > 0 )
	{
		$sql = 'SELECT id FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE id!=' . $id . ' ORDER BY status ASC';
		$result = $db->query( $sql );
		$status = 0;
		while( $row = $result->fetch() )
		{
			++$status;
			if( $status == $new_vid ) ++$status;
			$sql = 'UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_rows SET status=' . $status . ' WHERE id=' . $row['id'];
			$db->query( $sql );
		}
		$sql = 'UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_rows SET status=' . $new_vid . ' WHERE id=' . $id;
		$db->query( $sql );
		$content = 'OK_' . $id;
	}
	$nv_Cache->delMod( $module_name );
	include NV_ROOTDIR . '/includes/header.php';
	echo $content;
	include NV_ROOTDIR . '/includes/footer.php';
	exit();
}
if ( $nv_Request->isset_request( 'delete_id', 'get' ) and $nv_Request->isset_request( 'delete_checkss', 'get' ))
{
	$id = $nv_Request->get_int( 'delete_id', 'get' );
	$delete_checkss = $nv_Request->get_string( 'delete_checkss', 'get' );
	if( $id > 0 and $delete_checkss == md5( $id . NV_CACHE_PREFIX . $client_info['session_id'] ) )
	{
		$status=0;
		$sql = 'SELECT status FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE id =' . $db->quote( $id );
		$result = $db->query( $sql );
		list( $status) = $result->fetch( 3 );
		
		$db->query('DELETE FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows  WHERE id = ' . $db->quote( $id ) );
		if( $status > 0)
		{
			$sql = 'SELECT id, status FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE status >' . $status;
			$result = $db->query( $sql );
			while(list( $id, $status) = $result->fetch( 3 ))
			{
				$status--;
				$db->query( 'UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_rows SET status=' . $status . ' WHERE id=' . intval( $id ));
			}
		}
		$nv_Cache->delMod( $module_name );
		Header( 'Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op );
		die();
	}
}

$row = array();
$error = array();
$row['id'] = $nv_Request->get_int( 'id', 'post,get', 0 );
if ( $nv_Request->isset_request( 'submit', 'post' ) )
{
	$row['alias'] = $nv_Request->get_title( 'alias', 'post', '' );
	$row['module'] = $nv_Request->get_title( 'module', 'post', '' );
	$row['op'] = $nv_Request->get_title( 'op', 'post', '' );

	if( empty( $row['alias'] ) )
	{
		$error[] = $lang_module['error_required_alias'];
	}
	elseif( empty( $row['module'] ) )
	{
		$error[] = $lang_module['error_required_module'];
	}
	elseif( empty( $row['op'] ) )
	{
		$error[] = $lang_module['error_required_op'];
	}

	if( empty( $error ) )
	{
		try
		{
			if( empty( $row['id'] ) )
			{
				$stmt = $db->prepare( 'INSERT INTO ' . NV_PREFIXLANG . '_' . $module_data . '_rows (alias, module, op, weight, status) VALUES (:alias, :module, :op, :weight, :status)' );

				$stmt->bindValue( ':weight', 1, PDO::PARAM_INT );

				$weight = $db->query( 'SELECT max(status) FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows' )->fetchColumn();
				$weight = intval( $weight ) + 1;
				$stmt->bindParam( ':status', $weight, PDO::PARAM_INT );


			}
			else
			{
				$stmt = $db->prepare( 'UPDATE ' . NV_PREFIXLANG . '_' . $module_data . '_rows SET alias = :alias, module = :module, op = :op WHERE id=' . $row['id'] );
			}
			$stmt->bindParam( ':alias', $row['alias'], PDO::PARAM_STR );
			$stmt->bindParam( ':module', $row['module'], PDO::PARAM_STR );
			$stmt->bindParam( ':op', $row['op'], PDO::PARAM_STR );

			$exc = $stmt->execute();
			if( $exc )
			{
				$nv_Cache->delMod( $module_name );
				Header( 'Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op );
				die();
			}
		}
		catch( PDOException $e )
		{
			trigger_error( $e->getMessage() );
			die( $e->getMessage() ); //Remove this line after checks finished
		}
	}
}
elseif( $row['id'] > 0 )
{
	$row = $db->query( 'SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_rows WHERE id=' . $row['id'] )->fetch();
	if( empty( $row ) )
	{
		Header( 'Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op );
		die();
	}
}
else
{
	$row['id'] = 0;
	$row['alias'] = '';
	$row['module'] = '';
	$row['op'] = '';
}

$q = $nv_Request->get_title( 'q', 'post,get' );

// Fetch Limit
$show_view = false;
if ( ! $nv_Request->isset_request( 'id', 'post,get' ) )
{
	$show_view = true;
	$per_page = 20;
	$page = $nv_Request->get_int( 'page', 'post,get', 1 );
	$db->sqlreset()
		->select( 'COUNT(*)' )
		->from( '' . NV_PREFIXLANG . '_' . $module_data . '_rows' );

	if( ! empty( $q ) )
	{
		$db->where( 'alias LIKE :q_alias OR module LIKE :q_module OR op LIKE :q_op' );
	}
	$sth = $db->prepare( $db->sql() );

	if( ! empty( $q ) )
	{
		$sth->bindValue( ':q_alias', '%' . $q . '%' );
		$sth->bindValue( ':q_module', '%' . $q . '%' );
		$sth->bindValue( ':q_op', '%' . $q . '%' );
	}
	$sth->execute();
	$num_items = $sth->fetchColumn();

	$db->select( '*' )
		->order( 'status ASC' )
		->limit( $per_page )
		->offset( ( $page - 1 ) * $per_page );
	$sth = $db->prepare( $db->sql() );

	if( ! empty( $q ) )
	{
		$sth->bindValue( ':q_alias', '%' . $q . '%' );
		$sth->bindValue( ':q_module', '%' . $q . '%' );
		$sth->bindValue( ':q_op', '%' . $q . '%' );
	}
	$sth->execute();
}


$xtpl = new XTemplate( $op . '.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file );
$xtpl->assign( 'LANG', $lang_module );
$xtpl->assign( 'NV_LANG_VARIABLE', NV_LANG_VARIABLE );
$xtpl->assign( 'NV_LANG_DATA', NV_LANG_DATA );
$xtpl->assign( 'NV_BASE_ADMINURL', NV_BASE_ADMINURL );
$xtpl->assign( 'NV_NAME_VARIABLE', NV_NAME_VARIABLE );
$xtpl->assign( 'NV_OP_VARIABLE', NV_OP_VARIABLE );
$xtpl->assign( 'MODULE_NAME', $module_name );
$xtpl->assign( 'MODULE_UPLOAD', $module_upload );
$xtpl->assign( 'NV_ASSETS_DIR', NV_ASSETS_DIR );
$xtpl->assign( 'OP', $op );
$xtpl->assign( 'ROW', $row );

$xtpl->assign( 'Q', $q );

//print_r($site_mods);die;

foreach($site_mods as $mod)
{
	//print_r($mod);die;
	if($mod['module_file'] == 'shops' or $mod['module_file'] == 'about' or $mod['module_file'] == 'alias' or $mod['module_file'] == 'page' or $mod['module_file'] == 'news' or $mod['module_file'] == 'store' or $mod['module_file'] == 'download' )
	{
		$xtpl->assign( 'mod', $mod );
		$xtpl->parse( 'main.view.mod' );
	}
}

if( $show_view )
{
	$base_url = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op;
	if( ! empty( $q ) )
	{
		$base_url .= '&q=' . $q;
	}
	$generate_page = nv_generate_page( $base_url, $num_items, $per_page, $page );
	if( !empty( $generate_page ) )
	{
		$xtpl->assign( 'NV_GENERATE_PAGE', $generate_page );
		$xtpl->parse( 'main.view.generate_page' );
	}
	$number = $page > 1 ? ($per_page * ( $page - 1 ) ) + 1 : 1;
	$stt = 1;
	while( $view = $sth->fetch() )
	{
		for( $i = 1; $i <= $num_items; ++$i )
		{
			$xtpl->assign( 'WEIGHT', array(
				'key' => $i,
				'title' => $i,
				'selected' => ( $i == $view['status'] ) ? ' selected="selected"' : '') );
			$xtpl->parse( 'main.view.loop.status_loop' );
		}
		$xtpl->assign( 'CHECK', $view['weight'] == 1 ? 'checked' : '' );
		$view['link_edit'] = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;id=' . $view['id'];
		$view['link_delete'] = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;delete_id=' . $view['id'] . '&amp;delete_checkss=' . md5( $view['id'] . NV_CACHE_PREFIX . $client_info['session_id'] );
		
		$view['link'] = nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '='.$view['alias'],true);
		
		$xtpl->assign( 'VIEW', $view );
		$xtpl->assign( 'stt', $stt );
		$stt++;
		$xtpl->parse( 'main.view.loop' );
	}
	$xtpl->parse( 'main.view' );
}


if( ! empty( $error ) )
{
	$xtpl->assign( 'ERROR', implode( '<br />', $error ) );
	$xtpl->parse( 'main.error' );
}

$xtpl->parse( 'main' );
$contents = $xtpl->text( 'main' );

$page_title = $lang_module['main'];

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme( $contents );
include NV_ROOTDIR . '/includes/footer.php';
