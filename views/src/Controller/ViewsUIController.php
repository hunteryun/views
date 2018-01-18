<?php

namespace Hunter\views\Controller;

use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response\JsonResponse;
use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;
use NilPortugues\Sql\QueryBuilder\Syntax\OrderBy;
use Hunter\Core\Utility\StringConverter;
use Hunter\Core\Serialization\Yaml;

/**
 * Class ViewsUI.
 *
 * @package Hunter\views\Controller
 */
class ViewsUIController {
  /**
   * views_list.
   *
   * @return string
   *   Return views_list string.
   */
  public function views_list() {
    $list = views_get_all();
    return view('/admin/views-list.html', array('list' => $list));
  }

  /**
   * views_add_view.
   *
   * @return string
   *   Return views_add_view string.
   */
  public function views_add_view() {
    $tables = _views_get_tables();
    return view('/admin/views-add.html', array('tables' => $tables));
  }

  /**
   * views_settings.
   *
   * @return string
   *   Return views_settings string.
   */
public function views_settings(ServerRequest $request) {
    if ($parms = $request->getParsedBody()) {
      variable_set('views_show_sql', $parms['views_show_sql']);
      return true;
    }

    $form['views_show_sql'] = array(
      '#type' => 'checkboxes',
      '#title' => '显示SQL查询',
      '#required' => TRUE,
      '#default_value' => variable_get('views_show_sql', 0),
      '#options' => array('1' => '启用'),
      '#attributes' => array('lay-skin' => 'primary', 'value' => '1'),
    );
    $form['save'] = array(
     '#type' => 'submit',
     '#value' => t('Save'),
     '#attributes' => array('lay-submit' => '', 'lay-filter' => 'ViewsConfigUpdate'),
    );

    return view('/admin/views-settings.html', array('form' => $form));
  }

  /**
   * views_view_edit.
   *
   * @return string
   *   Return views_view_edit string.
   */
  public function views_view_edit($view) {
    $view_machine_name = 'views_view_final_'.$view;
    $view_config = variable_get($view_machine_name);
    $tables = _views_get_tables();
    return view('/admin/views-edit.html', array('tables' => $tables, 'view' => $view_config));
  }

  /**
   * views_view_export.
   *
   * @return string
   *   Return views_view_export string.
   */
  public function views_view_export($view) {
    $view_machine_name = 'views_view_final_'.$view;
    $view_config = variable_get($view_machine_name);
    $export_yml = Yaml::encode($view_config);
    hunter_download('sites/tmp/views/views_view_'.$view.'.yml', $export_yml);
  }

  /**
   * views_view_save.
   *
   * @return string
   *   Return views_view_save string.
   */
  public function views_view_save($parms) {
    if($parms) {
      $string = new StringConverter();
      $view_machine_name = $string->createMachineName($parms['view_name']);
      variable_set('views_view_'.$parms['type'].'_'.$view_machine_name, $parms);
    }
  }

  /**
   * views_view_delete.
   *
   * @return string
   *   Return views_view_delete string.
   */
  public function views_view_delete($view) {
    return 'Implement method: views_view_delete with parameter(s): '.$view;
  }

  /**
   * api_get_tables.
   *
   * @return string
   *   Return api_get_tables string.
   */
  public function api_get_machine_name(ServerRequest $request, StringConverter $string) {
    if($parms = $request->getParsedBody()){
      $machine_name = $string->createMachineName($parms['name']);
      return new JsonResponse($machine_name);
    }
    return new JsonResponse(false);
  }

  /**
   * api_get_tables.
   *
   * @return string
   *   Return api_get_tables string.
   */
  public function api_get_tables() {
    $tables = _views_get_tables();
    return new JsonResponse($tables);
  }

  /**
   * api_get_filter_ops.
   *
   * @return string
   *   Return api_get_filter_ops string.
   */
  public function api_get_filter_ops() {
    $operators['string'] = array(
      'equals' => array(
        'title' => 'Is equal to',
        'lable' => '=',
      ),
      'notEquals' => array(
        'title' => 'Is not equal to',
        'lable' => '!=',
      ),
      'like' => array(
        'title' => 'Like',
        'lable' => 'like',
      ),
      'notLike' => array(
        'title' => 'Not Like',
        'lable' => 'not like',
      ),
      'isNull' => array(
        'title' => 'Is empty (NULL)',
        'lable' => 'empty',
      ),
      'isNotNull' => array(
        'title' => 'Is not empty (NOT NULL)',
        'lable' => 'not empty',
      ),
    );
    $operators['number'] = array(
      'lessThan' => array(
        'title' => 'Is less than',
        'lable' => '<',
      ),
      'lessThanOrEqual' => array(
        'title' => 'Is less than or equal to',
        'lable' => '<=',
      ),
      'equals' => array(
        'title' => 'Is equal to',
        'lable' => '=',
      ),
      'notEquals' => array(
        'title' => 'Is not equal to',
        'lable' => '!=',
      ),
      'greaterThanOrEqual' => array(
        'title' => 'Is greater than or equal to',
        'lable' => '>=',
      ),
      'greaterThan' => array(
        'title' => 'Is greater than',
        'lable' => '>',
      ),
      'in' => array(
        'title' => 'Is in',
        'lable' => 'in',
      ),
      'notIn' => array(
        'title' => 'Is not in',
        'lable' => 'not in',
      ),
      'between' => array(
        'title' => 'Is between',
        'lable' => 'between',
      ),
      'notBetween' => array(
        'title' => 'Is not between',
        'lable' => 'not between',
      ),
    );
    $operators['yes-no'] = array(
      'equals' => array(
        'title' => 'Is equal to',
        'lable' => '=',
      ),
      'notEquals' => array(
        'title' => 'Is not equal to',
        'lable' => '!=',
      ),
    );

    return new JsonResponse($operators);
  }

  /**
   * api_ge_template_content.
   *
   * @return string
   *   Return api_ge_template_content string.
   */
  public function api_ge_template_content(ServerRequest $request) {
    if($parms = $request->getParsedBody()){
      if(file_exists($parms['file_name'])){
        $template_content = file_get_contents($parms['file_name']);
      }
      return new JsonResponse($template_content);
    }
    return new JsonResponse(false);
  }

  /**
   * api_get_query_result.
   *
   * @return string
   *   Return api_get_query_result string.
   */
  public function api_get_query_result(ServerRequest $request, GenericBuilder $builder) {
    if($parms = $request->getParsedBody()){
      $result = views_get_view($parms['view_machine_name'], true);

      if($result == false){
        return new JsonResponse('Empty Content !');
      }

      return new JsonResponse($result);
    }
    return new JsonResponse('Select Error !');
  }

  /**
   * api_save_view.
   *
   * @return string
   *   Return api_save_view string.
   */
  public function api_save_view(ServerRequest $request, GenericBuilder $builder, StringConverter $string) {
    if($parms = $request->getParsedBody()){
      if($parms['json_export'] == 'false'){
        if(!empty($parms['view_template'])){
          if(!empty($parms['template_content']) && $parms['overwrit_template'] == 'true' && $parms['type'] == 'final'){
            if (!is_dir(dirname($parms['view_template']))){
              mkdir(dirname($parms['view_template']), 0755, true);
            }

            file_put_contents($parms['view_template'], $parms['template_content']);
          }elseif (!empty($parms['template_content']) && $parms['type'] == 'temp') {
            $parms['view_template'] = 'sites/cache/views/views_view_cache_'.$parms['view_machine_name'];
            if (!is_dir(dirname($parms['view_template']))){
              mkdir(dirname($parms['view_template']), 0755, true);
            }

            file_put_contents($parms['view_template'], $parms['template_content']);
          }elseif (!empty($parms['template_content']) && $parms['type'] == 'final' && $parms['overwrit_template'] == 'false') {
            $parms['view_template'] = 'theme/'. $GLOBALS['default_theme'].'/views/'.basename($parms['view_template']);
            if (!is_dir(dirname($parms['view_template']))){
              mkdir(dirname($parms['view_template']), 0755, true);
            }

            file_put_contents($parms['view_template'], $parms['template_content']);
          }
        }else {
          if(!empty($parms['template_content']) && $parms['type'] == 'final'){
            $view_machine_name = $string->createMachineName($parms['view_machine_name']);
            $parms['view_template'] = 'theme/'. $GLOBALS['default_theme'].'/views/views-view-'.$view_machine_name.'.html';
            if (!is_dir(dirname($parms['view_template']))){
              mkdir(dirname($parms['view_template']), 0755, true);
            }

            file_put_contents($parms['view_template'], $parms['template_content']);
          }elseif (!empty($parms['template_content']) && $parms['type'] == 'temp') {
            $parms['view_template'] = 'sites/cache/views/views_view_cache_'.$parms['view_machine_name'];
            if (!is_dir(dirname($parms['view_template']))){
              mkdir(dirname($parms['view_template']), 0755, true);
            }
            file_put_contents($parms['view_template'], $parms['template_content']);
          }
        }
      }

      $tables = _views_get_tables();
      $lfields = $rfields = array();

      foreach ($parms['view_fields'] as $key => $field) {
        if(substr($field,0,strrpos($field,'.')) == $parms['view_table']){
          $lfields[] = str_replace($parms['view_table'].'.','',$field);
        }else {
          $rfields[] = str_replace($parms['view_relation_table'].'.','',$field);
        }
      }

      if(!empty($parms['view_sorts'])){
        foreach ($parms['view_sorts'] as $sort) {
          if(substr($sort['field'],0,strrpos($sort['field'],'.')) == $parms['view_table']){
            $sort['field'] = str_replace($parms['view_table'].'.','',$sort['field']);
            $lsorts[] = $sort;
          }else {
            $sort['field'] = str_replace($parms['view_relation_table'].'.','',$sort['field']);
            $rsorts[] = $sort;
          }
        }
      }

      $query = $builder->select()->setTable($parms['view_table']);
      $query->setColumns($lfields);

      if(!empty($lsorts)){
        foreach ($lsorts as $lsort) {
          if($lsort['value'] == 'desc'){
            $query->orderBy($lsort['field'], OrderBy::DESC);
          }else {
            $query->orderBy($lsort['field'], OrderBy::ASC);
          }
        }
      }

      if($rfields){
        $query->innerJoin(
          $parms['view_relation_table'], //join table
          'uid', //origin table field used to join
          'uid', //join column
           $rfields
        );
      }

      if(!empty($parms['view_filters'])){
        foreach ($parms['view_filters'] as $filter) {
          $op = $filter['op'];
          if(strpos($filter['value'],'-') !== false && ($filter['op'] == 'between' || $filter['op'] == 'notBetween')){
            $v = explode('-', $filter['value']);
            $query->where()
            ->$op(substr($filter['field'], strrpos($filter['field'],'.')+1), $v[0], $v[1]);
          }elseif($filter['op'] == 'in' || $filter['op'] == 'notIn') {
            $v = explode(',', $filter['value']);
            $query->where()
            ->$op(substr($filter['field'], strrpos($filter['field'],'.')+1), $v);
          }else {
            $query->where()
            ->$op(substr($filter['field'], strrpos($filter['field'],'.')+1), $filter['value']);
          }
        }
      }

      if(!empty($rsorts)){
        foreach ($rsorts as $rsort) {
          if($rsort['value'] == 'desc'){
            $query->orderBy($rsort['field'], OrderBy::DESC, $parms['view_relation_table']);
          }else {
            $query->orderBy($rsort['field'], OrderBy::ASC, $parms['view_relation_table']);
          }
        }
      }

      $parms['view_query'] = $builder->write($query);
      $parms['view_query_values'] = $builder->getValues();

      if(is_string($parms['view_query'])){
        $this->views_view_save($parms);
      }

      if($parms['type'] == 'temp'){
        if($parms['json_export'] == 'true'){
          return new JsonResponse('json_export');
        }else {
          return new JsonResponse($parms['view_template']);
        }
      }

      return new JsonResponse(true);
    }

    return new JsonResponse(false);
  }

  /**
   * api_get_templates.
   *
   * @return string
   *   Return api_get_templates string.
   */
  public function api_get_templates(ServerRequest $request) {
    $parms = $request->getParsedBody();
    if(isset($parms['rescan'])){
      $pattern = '/^' . preg_quote('views-view-', '/') . '.*' . preg_quote('.html', '/') . '$/';
      $alltemplates = array_merge(file_scan('module', $pattern, array('minDepth'=>2)), file_scan('theme', $pattern, array('minDepth'=>2)));

      if(!empty($alltemplates)){
        foreach ($alltemplates as $template) {
          if(file_exists($template['file'])){
            variable_set($template['basename'], $template['file']);
          }
        }
      }
    }

    $list = views_get_templates();

    if(!empty($list)){
      foreach ($list as $name => $value) {
        if(!file_exists($value)){
          unset($list[$name]);
          variable_del($name);
        }
      }
    }

    return new JsonResponse($list);
  }

  /**
   * api_get_permissions.
   *
   * @return string
   *   Return api_get_permissions string.
   */
  public function api_get_permissions(ServerRequest $request) {
    global $app;
    $permissions = array();
    foreach ($app->getPermissionsList() as $key => $item) {
      $permissions[] = array(
        'id' => $key,
        'name' => $item['title']
      );
    }
    return new JsonResponse($permissions);
  }

  /**
   * api_get_views_setting.
   *
   * @return string
   *   Return api_get_views_setting string.
   */
  public function api_get_views_setting(ServerRequest $request) {
    $setting['views_show_sql'] = variable_get('views_show_sql', 0);
    return new JsonResponse($setting);
  }

  /**
   * api_get_view.
   *
   * @return string
   *   Return api_get_view string.
   */
   public function api_get_view(ServerRequest $request, $vars) {
     $view = views_get_view_bypath(request_uri());
     if($view){
       if(isset($view['view_query']) && isset($view['view_template'])){
         if($view['has_pager'] && !empty($view['view_pager'])){
           $page = isset($_GET['page']) ? $_GET['page'] : 1;
           $offset = (int)$view['view_pager']['offset'];
           $number_perpage = 999999;

           if($view['view_pager']['type'] != 'display_all'){
             $offset = ((int)$page-1) * (int)$view['view_pager']['display'] + (int)$view['view_pager']['offset'];
             $pager['page'] = $page;
             $pager['size'] = (int)$view['view_pager']['display'];
             $pager['total'] = COUNT(db_query($view['view_query'], $view['view_query_values'])->fetchAll());
             if($preview){
               $pager['url'] = '/admin/views/add';
             }
             $number_perpage = (int) $view['view_pager']['display'];

             theme()->getEnvironment()->addGlobal('pager', $pager);
           }

           $view['view_query'] = $view['view_query']. ' LIMIT ' . $offset . ', ' . $number_perpage;
         }

         foreach ($view['view_query_values'] as $key => $value) {
           if(strpos($value,':::') !== false){
             $v = explode(':::', $value);
             $view['view_query_values'][$key] = $vars[$v[0]];
           }
         }

         $result = db_query($view['view_query'], $view['view_query_values'])->fetchAll();
         if(!empty($result)) {
           if($view['json_export'] == 'false'){
             $output = theme()->render($view['view_template'], array('viewdata' => $result));
             return $output;
           }else{
             return new JsonResponse($result);
           }
         }else {
           return 'Empty Content !';
         }
       }
     }
     return false;
   }
}
