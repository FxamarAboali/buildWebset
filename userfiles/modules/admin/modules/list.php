 
<? 
$modules_options = array();
$modules_options['skip_admin'] = true;
$modules_options['ui'] = true;
 
 if(!isset($modules ) ){
$modules = get_modules($modules_options );
 }
//

?>

<ul class="modules-list">
  <? foreach($modules as $module2): ?>
  <?
		//d($module2);
		 
		 $module_group2 = explode(DIRECTORY_SEPARATOR ,$module2['module']);
		 $module_group2 = $module_group2[0];
		?>
   <? $module2['module'] = str_replace('\\','/',$module2['module']); ?>
  <? $module2['module_clean'] = str_replace('/','__',$module2['module']); ?>
  <? $module2['name_clean'] = str_replace('/','-',$module2['module']); ?>
  <? $module2['name_clean'] = str_replace(' ','-',$module2['name_clean']); ?>
  <li data-filter="<? print $module2['name'] ?>" data-category="<? isset($module2['categories'])? print addslashes($module2['categories']) : ''; ?>" class="module-item" alt="<? isset($module2['description'])? print addslashes($module2['description']) : ''; ?>">
  <span class="mw_module_hold">
    <? if($module2['icon']): ?>


          <span class="mw_module_image">
            <span class="mw_module_image_shadow"></span>
            <img
                alt="<? print $module2['name'] ?>"
                title="<? isset($module2['description'])? print addslashes($module2['description']) : ''; ?>"
                class="module_draggable"
                data-module-name="<? print $module2['module'] ?>"
                data-module-name-enc="<? print $module2['module_clean'] ?>|<? print $module2['name_clean'] ?>_<? print date("YmdHis") ?>"
                src="<? print $module2['icon'] ?>"
                 />
          </span>



    <? endif; ?>
    <span class="module_name" alt="<? isset($module2['description'])? print addslashes($module2['description']) : ''; ?>"><? print $module2['name'] ?></span>
    </span>
   </li>
    <? endforeach; ?>
  <? foreach($modules as $module): ?>
  <?
 $module_group = explode(DIRECTORY_SEPARATOR ,$module['module']);
 $module_group = $module_group[0];
 $showed_module_groups = array();

?>
  <? if(!in_array($module_group, $showed_module_groups))  : ?>
  <? endif; ?>
  <?  $showed_module_groups[] = $module_group; ?>
  <? endforeach; ?>
</ul>
 
