<?php
/*
 * This file is part of the Microweber framework.
 *
 * (c) Microweber LTD
 *
 * For full license information see
 * http://Microweber.com/license/
 *
 */
namespace MicroweberPackages\DatabaseManager;

class Crud
{
    /** @var Application */
    public $app;

    public $table = 'your_table_name_here';

    public function __construct($app = null)
    {
         if (!is_object($this->app)) {
            if (is_object($app)) {
                $this->app = $app;
            }
        }
        if (!$this->app) {
            $this->app = app();   
        }
    }

    public function get($params)
    {
        if (is_string($params)) {
            $params = parse_params($params);
        }
        if ($params == false) {
            return;
        }

        $table = $this->table;
        $params['table'] = $table;

        $enable_trigers = true;
        if (isset($params['enable_trigers'])) {
            $enable_trigers = $params['enable_trigers'];
        }

        if ($enable_trigers) {
            $override = $this->app->event_manager->trigger('mw.crud.' . $table . '.get.params', $params);
            if (is_array($override)) {
                foreach ($override as $resp) {
                    if (is_array($resp) and !empty($resp)) {
                        $params = array_merge($params, $resp);
                    }
                }
            }
        }

        $get = $this->app->database_manager->get($params);

        $override_data = array();

        $is_single_item = false;
        if (isset($params['single']) and $params['single']) {
            $is_single_item = true;
            $override_data[0] = $get;
        } else {
            $override_data = $get;

        }
        if ($override_data) {
            if (isset($params['count']) and $params['count']) {
                //do nothing on override
            } else {
                $override = $this->app->event_manager->trigger('mw.crud.' . $table . '.get', $override_data);
                if (is_array($override)) {
                    foreach ($override as $resp) {
                        if (is_array($resp) and !empty($resp)) {
                           $override_data = $resp;
                            if($is_single_item){
                                $get =  $override_data[0] ;
                            } else {
                                $get =  $override_data;
                            }
                          }
                    }
                }
            }
        }

        return $get;
    }

    public function get_by_id($id = 0, $field_name = 'id')
    {
        if ($field_name == 'id') {
            $id = intval($id);
            if ($id == 0) {
                return false;
            }
        }
        if ($field_name == false) {
            $field_name = 'id';
        }
        $table = $this->table;
        $params = array();
        $params[$field_name] = $id;
        $params['table'] = $table;
        $params['single'] = true;
        $data = $this->get($params);

        return $data;
    }

    public function save($params)
    {
        if (is_string($params)) {
            $params = parse_params($params);
        }
        if ($params == false) {
            return;
        }

        $table = $this->table;
        $params['table'] = $table;
        $save = $this->app->database_manager->save($params);

        return $save;
    }

    public function delete($data)
    {
        if (!is_array($data)) {
            $id = intval($data);
            $data = array('id' => $id);
        }
        if (!isset($data['id']) or $data['id'] == 0) {
            return false;
        }
        $table = $this->table;

        return $this->app->database_manager->delete_by_id($table, $id = $data['id'], $field_name = 'id');
    }

    public function has_permission($params)
    {
        if (!is_logged()) {
            return;
        }
        if (isset($params['id']) and $params['id'] != 0) {
            $check_author = $this->get_by_id($params['id']);
            $author_id = user_id();
            if (isset($check_author['created_by']) and ($check_author['created_by'] == $author_id)) {
                return true;
            }
        } elseif (!isset($params['id']) or $params['id'] == 0) {
            return true;
        }
    }

    public function table()
    {
        $table = $this->table;

        return $this->app->database_manager->table($table);
    }
}
