<?php

namespace Hunter\views\Controller;

use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response\JsonResponse;
use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;
use NilPortugues\Sql\QueryBuilder\Syntax\OrderBy;
use Hunter\Core\Utility\StringConverter;

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
    return view('/admin/views_list.html', array('list' => $list));
  }

  /**
   * views_add_view.
   *
   * @return string
   *   Return views_add_view string.
   */
  public function views_add_view() {
    $tables = _views_get_tables();
    return view('/admin/views_add.html', array('tables' => $tables));
  }

  /**
   * views_settings.
   *
   * @return string
   *   Return views_settings string.
   */
  public function views_settings() {
    return 'Implement method: views_settings';
  }

  /**
   * views_view_edit.
   *
   * @return string
   *   Return views_view_edit string.
   */
  public function views_view_edit($view) {
    $view_name = 'views.view.'.$view.'.yml';
    $view_config = get_view_byname($view_name);
    $tables = _views_get_tables();
    return view('/admin/views_edit.html', array('tables' => $tables, 'view' => $view_config));
  }

  /**
   * views_view_save.
   *
   * @return string
   *   Return views_view_save string.
   */
  public function views_view_save($parms) {
    $stringConverter = new StringConverter();
    if($parms) {
      $view_machine_name = $stringConverter->createMachineName($parms['view_name']);
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
      $result = views_get_view($parms['view_name'], true);

      if(is_string($result)){
        return new JsonResponse($result);
      }

      return new JsonResponse('Empty Content !');
    }
    return new JsonResponse('Select Error !');
  }

  /**
   * api_save_view.
   *
   * @return string
   *   Return api_save_view string.
   */
  public function api_save_view(ServerRequest $request, GenericBuilder $builder, StringConverter $stringConverter) {
    if($parms = $request->getParsedBody()){
      if(!empty($parms['view_template'])){
        if(!empty($parms['template_content']) && $parms['overwrit_template'] == 'true' && $parms['type'] == 'final'){
          if (!is_dir(dirname($parms['view_template']))){
            mkdir(dirname($parms['view_template']), 0755, true);
          }

          file_put_contents($parms['view_template'], $parms['template_content']);
        }elseif (!empty($parms['template_content']) && $parms['type'] == 'temp') {
          $parms['view_template'] = 'sites/cache/views/views_view_cache_'.$parms['view_name'];
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
          $view_machine_name = $stringConverter->createMachineName($parms['view_name']);
          $parms['view_template'] = 'theme/'. $GLOBALS['default_theme'].'/views/views-view-'.$view_machine_name.'.html';
          if (!is_dir(dirname($parms['view_template']))){
            mkdir(dirname($parms['view_template']), 0755, true);
          }

          file_put_contents($parms['view_template'], $parms['template_content']);
        }elseif (!empty($parms['template_content']) && $parms['type'] == 'temp') {
          $parms['view_template'] = 'sites/cache/views/views_view_cache_'.$parms['view_name'];
          if (!is_dir(dirname($parms['view_template']))){
            mkdir(dirname($parms['view_template']), 0755, true);
          }
          file_put_contents($parms['view_template'], $parms['template_content']);
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
        return new JsonResponse($parms['view_template']);
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

}
