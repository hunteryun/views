<?php

namespace Hunter\views\Controller;

use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response\JsonResponse;
use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;
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

      return new JsonResponse(false);
    }
    return new JsonResponse(false);
  }

  /**
   * api_save_view.
   *
   * @return string
   *   Return api_save_view string.
   */
  public function api_save_view(ServerRequest $request, GenericBuilder $builder) {
    if($parms = $request->getParsedBody()){

      if(!empty($parms['view_template']) && !empty($parms['template_content']) && $parms['overwrit_template'] && $parms['type'] == 'final'){
        if (!is_dir(dirname($parms['view_template']))){
          mkdir(dirname($parms['view_template']), 0755, true);
        }

        file_put_contents($parms['view_template'], $parms['template_content']);
      }elseif (!empty($parms['view_template']) && !empty($parms['template_content']) && $parms['overwrit_template'] && $parms['type'] == 'temp') {
        $temp_template_path = 'sites/cache/views/views_view_cache_'.$parms['view_name'];
        if (!is_dir(dirname($temp_template_path))){
          mkdir(dirname($temp_template_path), 0755, true);
        }
        file_put_contents($temp_template_path, $parms['template_content']);

        $parms['view_template'] = $temp_template_path;
      }

      $query = $builder->select()
      ->setTable($parms['view_table'])
      ->setColumns($parms['view_fields'])
      ->end();

      $parms['view_query'] = $builder->write($query);

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
