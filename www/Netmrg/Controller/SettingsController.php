<?php
/**
 * Part of NetMRG2
 * Copyright (c) 2014
 *   Marcus Schwarz <msspamfang@gmx.de>
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 * @author Marcus Schwarz <msspamfang@gmx.de>
 */

namespace Netmrg\Controller;

use Netmrg\Auth;
use Netmrg\BaseController;
use Netmrg\Configuration;
use Netmrg\Exception\ForbiddenException;
use Netmrg\Graphs;
use Netmrg\Helper;
use Netmrg\ScriptTest;
use Netmrg\SQLTest;
use Netmrg\User;

class SettingsController
    extends BaseController
{

    public function indexAction()
    {
        $this->minPermission(Auth::RIGHT_READALL);

        $this->add('menu', 'settingsindex');

        $this->load('settings/index');
        $this->render();
    }

    public function devicesAction()
    {
        $this->minPermission(Auth::RIGHT_READALL);

        $this->add('menu', 'settingsdevices');

        $this->load('settings/index');
        $this->render();
    }

    public function graphsAction()
    {
        $order = (isset($_GET['order_by'])) ? $_GET['order_by'] : 'name';
        $type = (isset($_GET['type']) && $_GET['type'] == 'template') ? 'template' : 'custom';

        $this->minPermission(Auth::RIGHT_READALL);
        $this->add('menu', 'settings' . $type . 'graphs');

        $s = getDatabase()->prepare(
            'SELECT id, type, name FROM graphs WHERE type = :type ORDER BY :order'
        );
        $s->bindValue(':type', $type);
        $s->bindValue(':order', $order);
        $s->execute();
        $scripts = $s->fetchAll(\PDO::FETCH_ASSOC);

        $this->load('settings/graphs');
        $this->render(
            array(
                'graphs' => $scripts,
                'sumgraphs' => count($scripts),
                'graphtype' => ucfirst($type)
            )
        );
    }

    public function graphs_deleteAction()
    {
        $this->minPermission(Auth::RIGHT_READWRITE);
        $type = (isset($_GET['type']) && strtolower(
                $_GET['type']
            ) == 'template') ? 'template' : 'custom';
        $this->testForPresence(
            array(
                'post' => array('csrftoken')
            )
        );

        if (!$this->isValidCsrfToken($_POST['csrftoken'])) {
            throw new ForbiddenException();
        }

        $deleteIds = array();
        if (isset($_GET['id'])) {
            $deleteIds[] = $_GET['id'];
        } elseif (isset($_POST['delids'])) {
            $deleteIds = explode(',', $_POST['delids']);
        }

        foreach ($deleteIds as $id) {
            Graphs::delete($id);
        }
        $this->redirect('/settings/graphs?type=' . $type);
    }

    public function graphs_duplicateAction()
    {
        $this->minPermission(Auth::RIGHT_READWRITE);
        $type = (isset($_GET['type']) && strtolower(
                $_GET['type']
            ) == 'template') ? 'template' : 'custom';
        $this->testForPresence(
            array(
                'get' => array('id')
            )
        );


        $graph = new Graphs($_GET['id']);
        $graph->duplicate();

        $this->redirect('/settings/graphs?type=' . $type);
    }

    public function graphs_applyAction()
    {
        $this->minPermission(Auth::RIGHT_READWRITE);
        $this->testForPresence(
            array(
                'get' => array('id')
            )
        );


        $listOfGraphs = $this->mapListOfTemplateGraphs(intval($_GET['id']));
        $subdevices = getDatabase()->query('SELECT devices.name AS dev_name, sub_devices.name AS sub_name, sub_devices.id AS id FROM sub_devices LEFT JOIN devices ON sub_devices.dev_id = devices.id ORDER BY dev_name, sub_name, id')->fetchAll(\PDO::FETCH_ASSOC);


        $this->load('settings/applygraphs');
        $this->render(array('listofgraphs' => $listOfGraphs, 'subdevices' => $subdevices));


    }

    private function mapListOfTemplateGraphs($id) {
        $s = getDatabase()->query('SELECT id, name FROM graphs WHERE type = "template"')->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($s as $idx => $val) {
            if ($val['id'] == $id) {
                $s[$idx]['checked'] = 'checked="checked"';
                break;
            }
        }
        return $s;
    }

    public function graphs_addAction()
    {
        $this->minPermission(Auth::RIGHT_READWRITE);
        $type = (isset($_GET['type']) && strtolower(
                $_GET['type']
            ) == 'template') ? 'template' : 'custom';
        $this->add('menu', 'settings' . $type . 'graphs');

        $graph = ($this->hasSavedForm('Graphs')) ? $this->formGet() : new Graphs();
        $this->load('settings/addgraph');
        $this->render(array('graphtype' => ucfirst($type), 'graph' => $graph));
    }

    public function graphs_editAction()
    {
        $this->minPermission(Auth::RIGHT_READWRITE);
        $type = (isset($_GET['type']) && strtolower(
                $_GET['type']
            ) == 'template') ? 'template' : 'custom';

        $this->add('menu', 'settings' . $type . 'graphs');

        $this->testForPresence(array('get' => array('id')));

        $graph = new Graphs(intval($_GET['id']));

        $options = explode(',', $graph->options);
        $hidelegend = '';
        $logarithmic = '';
        if (in_array('nolegend', $options)) {
            $hidelegend = ' checked="checked"';
        }
        if (in_array('logarithmic', $options)) {
            $logarithmic = ' checked="checked"';
        }


        $this->load('settings/editgraph');
        $this->render(
            array(
                'graphtype' => $type,
                'graph' => $graph,
                'hidelegend' => $hidelegend,
                'logarithmic' => $logarithmic
            )
        );
    }

    public function graphs_patchAction()
    {
        $this->minPermission(Auth::RIGHT_READWRITE);
        $this->testForPresence(
            array(
                'post' => array(
                    'id',
                    'csrftoken',
                    'name',
                    'title',
                    'comment',
                    'width',
                    'height',
                    'verticallabel',
                    'type'
                )
            )
        );
        if (!$this->isValidCsrfToken($_POST['csrftoken'])) {
            throw new ForbiddenException();
        }
        if (empty($_POST['id']) || empty($_POST['name']) || empty($_POST['title']) || empty($_POST['width']) || empty($_POST['height'])) {
            $this->errors->append('Empty fields are not allowed!');
        }

        //todo maybe add some more validity checks
        if (!is_numeric($_POST['width']) || !is_numeric($_POST['height'])) {
            $this->errors->append(
                'The "width"- and "height"-fields must be numeric'
            );
        } else {
            $_POST['width'] = abs($_POST['width']);
            $_POST['height'] = abs($_POST['height']);
        }
        $type = (!in_array(
            strtolower($_POST['type']),
            array('custom', 'template')
        )) ? 'custom' : 'template';

        $min = (is_numeric($_POST['min'])) ? $_POST['min'] : null;
        $max = (is_numeric($_POST['max'])) ? $_POST['max'] : null;

        $options = array();

        if (!empty($_POST['hidelegend'])) {
            $options[] = 'nolegend';
        }
        if (!empty($_POST['logarithmic'])) {
            $options[] = 'logarithmic';
        }

        $test = new Graphs(array(
            'id' => $_POST['id'],
            'name' => $_POST['name'],
            'title' => $_POST['title'],
            'comment' => $_POST['comment'],
            'verticallabel' => $_POST['verticallabel'],
            'width' => $_POST['width'],
            'height' => $_POST['height'],
            'base' => $_POST['base'],
            'min' => $min,
            'max' => $max,
            'type' => $type,
            'options' => implode(',', $options)

        ));
        if (!$this->hasErrors()) {
            $test->save();
            $this->success->append(
                'The ' . ucfirst($type) . ' Test ' . $_POST['name'] . ' has been created'
            );
            $this->redirect('/settings/graphs?type=' . $type);
        } else {
            $this->formSave($test);
            $this->redirect('/settings/graphs/edit?id=' . intval($_POST['id']) . '&type=' . $type);
        }
    }

    public function graphs_createAction()
    {
        $this->minPermission(Auth::RIGHT_READWRITE);
        $this->testForPresence(
            array(
                'post' => array(
                    'csrftoken',
                    'id',
                    'name',
                    'title',
                    'comment',
                    'verticallabel',
                    'width',
                    'height',
                    'type',
                    'base',
                    'min',
                    'max'
                )
            )
        );
        if (!$this->isValidCsrfToken($_POST['csrftoken'])) {
            throw new ForbiddenException();
        }
        if (empty($_POST['name']) || empty($_POST['title']) || empty($_POST['width']) || empty($_POST['height'])) {
            $this->errors->append('Empty fields are not allowed!');
        }

        //todo maybe add some more validity checks
        if (!is_numeric($_POST['width']) || !is_numeric($_POST['height'])) {
            $this->errors->append(
                'The "width"- and "height"-fields must be numeric'
            );
        } else {
            $_POST['width'] = abs($_POST['width']);
            $_POST['height'] = abs($_POST['height']);
        }
        $type = (!in_array(
            strtolower($_POST['type']),
            array('custom', 'template')
        )) ? 'custom' : 'template';

        $min = (is_numeric($_POST['min'])) ? $_POST['min'] : null;
        $max = (is_numeric($_POST['max'])) ? $_POST['max'] : null;

        $options = array();

        if (!empty($_POST['hidelegend'])) {
            $options[] = 'nolegend';
        }
        if (!empty($_POST['logarithmic'])) {
            $options[] = 'logarithmic';
        }

        $test = new Graphs(array(
            'id' => $_POST['id'],
            'name' => $_POST['name'],
            'title' => $_POST['title'],
            'comment' => $_POST['comment'],
            'verticallabel' => $_POST['verticallabel'],
            'width' => $_POST['width'],
            'height' => $_POST['height'],
            'base' => $_POST['base'],
            'min' => $min,
            'max' => $max,
            'type' => $type,
            'options' => implode(',', $options)

        ));
        if (!$this->hasErrors()) {
            $test->save();
            $this->success->append(
                'The ' . $type . ' Graph ' . $_POST['name'] . ' has been created'
            );
            $this->redirect('/settings/graphs?type=' . $type);
        } else {
            $this->formSave($test);
            $this->redirect('/settings/graphs/add?type=' . $type);
        }
    }

    public function scriptsAction()
    {
        $this->minPermission(Auth::RIGHT_READWRITE);
        $this->add('menu', 'settingsscripts');


        $scripts = getDatabase()
            ->query(
                'SELECT id, name, cmd, data_type FROM tests_script ORDER BY name'
            )
            ->fetchAll(\PDO::FETCH_ASSOC);

        $this->mapScriptsDatatypes($scripts);
        $this->load('settings/scripts');
        $this->render(array('scripts' => $scripts, 'sumscripts' => count($scripts)));
    }

    private function mapScriptsDatatypes(&$scripts)
    {
        foreach ($scripts as $idx => $script) {
            $scripts[$idx]['data_type_name'] = ScriptTest::getDataTypeName($script['data_type']);
        }
    }

    public function scripts_deleteAction()
    {
        $this->minPermission(Auth::RIGHT_READWRITE);
        $this->testForPresence(
            array(
                'post' => array('csrftoken')
            )
        );

        if (!$this->isValidCsrfToken($_POST['csrftoken'])) {
            throw new ForbiddenException();
        }

        $deleteIds = array();
        if (isset($_GET['id'])) {
            $deleteIds[] = $_GET['id'];
        } elseif (isset($_POST['delids'])) {
            $deleteIds = explode(',', $_POST['delids']);
        }

        foreach ($deleteIds as $id) {
            SQLTest::delete($id);
        }
        $this->redirect('/settings/sql');
    }

    public function scripts_addAction()
    {
        $this->minPermission(Auth::RIGHT_READWRITE);
        $this->add('menu', 'settingssql');

        $test = ($this->hasSavedForm('SQLTest')) ? $this->formGet() : new SQLTest();
        $this->load('settings/addsql');
        $this->render(array('devices' => Helper::getDevices($test->sub_dev_type), 'sql' => $test));
    }

    public function scripts_editAction()
    {
        $this->minPermission(Auth::RIGHT_READWRITE);
        $this->add('menu', 'settingsscripts');
        $this->testForPresence(array('get' => array('id')));

        $test = new ScriptTest(intval($_GET['id']));
        $this->load('settings/editscript');
        $this->render(
            array(
                'datatypes' => Helper::getDataTypes($test->data_type),
                'devices' => Helper::getDevices($test->dev_type),
                'script' => $test
            )
        );
    }

    public function scripts_patchAction()
    {
        $this->minPermission(Auth::RIGHT_READWRITE);
        $this->testForPresence(
            array(
                'post' => array(
                    'id',
                    'csrftoken',
                    'name',
                    'host',
                    'user',
                    'password',
                    'query',
                    'device',
                    'columnnumber',
                    'timeout'
                )
            )
        );
        if (!$this->isValidCsrfToken($_POST['csrftoken'])) {
            throw new ForbiddenException();
        }
        if (empty($_POST['id']) || empty($_POST['name']) || empty($_POST['host']) || empty($_POST['user']) || empty($_POST['password']) || empty($_POST['query']) || empty($_POST['device']) || empty($_POST['columnnumber']) || empty($_POST['timeout'])) {
            $this->errors->append('Empty fields are not allowed!');
        }

        if (mb_strlen($_POST['query'] > 255)) {
            $this->errors->append(
                'The SQL query must not be longer than 255 characters, detected: ',
                mb_strlen($_POST['query'])
            );
        }

        if (!is_numeric($_POST['columnnumber'])) {
            $this->errors->append('Column Number must be a positive int');
        } else {
            $_POST['columnnumber'] = abs($_POST['columnnumber']);
        }
        if (!is_numeric($_POST['timeout'])) {
            $this->errors->append('Timeout must be a positive int');
        } else {
            $_POST['timeout'] = abs($_POST['timeout']);
        }

        // more tests omitted....

        $test = new SQLTest(array(
            'id' => $_POST['id'],
            'name' => $_POST['name'],
            'sub_dev_type' => $_POST['device'],
            'password' => $_POST['password'],
            'host' => $_POST['host'],
            'user' => $_POST['user'],
            'query' => $_POST['query'],
            'column_num' => $_POST['columnnumber'],
            'timeout' => $_POST['timeout']
        ));
        if (!$this->hasErrors()) {
            $test->save();
            $this->success->append('The SQL Test ' . $_POST['name'] . ' has been created');
            $this->redirect('/settings/sql');
        } else {
            $this->formSave($test);
            $this->redirect('/settings/sql/edit?id=' . intval($_POST['id']));
        }
    }

    public function scripts_createAction()
    {
        $this->minPermission(Auth::RIGHT_READWRITE);
        $this->testForPresence(
            array(
                'post' => array(
                    'csrftoken',
                    'name',
                    'host',
                    'user',
                    'password',
                    'query',
                    'device',
                    'columnnumber',
                    'timeout'
                )
            )
        );
        if (!$this->isValidCsrfToken($_POST['csrftoken'])) {
            throw new ForbiddenException();
        }
        if (empty($_POST['name']) || empty($_POST['host']) || empty($_POST['user']) || empty($_POST['password']) || empty($_POST['query']) || empty($_POST['device']) || empty($_POST['columnnumber']) || empty($_POST['timeout'])) {
            $this->errors->append('Empty fields are not allowed!');
        }

        if (mb_strlen($_POST['query'] > 255)) {
            $this->errors->append(
                'The SQL query must not be longer than 255 characters, detected: ',
                mb_strlen($_POST['query'])
            );
        }

        if (!is_numeric($_POST['columnnumber'])) {
            $this->errors->append('Column Number must be a positive int');
        } else {
            $_POST['columnnumber'] = abs($_POST['columnnumber']);
        }
        if (!is_numeric($_POST['timeout'])) {
            $this->errors->append('Timeout must be a positive int');
        } else {
            $_POST['timeout'] = abs($_POST['timeout']);
        }

        // more tests omitted....

        $test = new SQLTest(array(
            'name' => $_POST['name'],
            'sub_dev_type' => $_POST['device'],
            'password' => $_POST['password'],
            'host' => $_POST['host'],
            'user' => $_POST['user'],
            'query' => $_POST['query'],
            'column_num' => $_POST['columnnumber'],
            'timeout' => $_POST['timeout']
        ));
        if (!$this->hasErrors()) {
            $test->save();
            $this->success->append('The SQL Test ' . $_POST['name'] . ' has been created');
            $this->redirect('/settings/sql');
        } else {
            $this->formSave($test);
            $this->redirect('/settings/sql/add');
        }
    }

    public function sqlAction()
    {
        $this->minPermission(Auth::RIGHT_READWRITE);
        $this->add('menu', 'settingssql');


        $sql = getDatabase()
            ->query(
                'SELECT id, name, host, user, IF(LENGTH(query) > 75, CONCAT(SUBSTRING(query, 1, 70), "..."), query) AS query, query AS fullquery FROM tests_sql ORDER BY name'
            )
            ->fetchAll(\PDO::FETCH_ASSOC);

        $this->load('settings/sql');
        $this->render(array('sql' => $sql, 'sumsql' => count($sql)));
    }

    public function sql_deleteAction()
    {
        $this->minPermission(Auth::RIGHT_READWRITE);
        $this->testForPresence(
            array(
                'post' => array('csrftoken')
            )
        );

        if (!$this->isValidCsrfToken($_POST['csrftoken'])) {
            throw new ForbiddenException();
        }

        $deleteIds = array();
        if (isset($_GET['id'])) {
            $deleteIds[] = $_GET['id'];
        } elseif (isset($_POST['delids'])) {
            $deleteIds = explode(',', $_POST['delids']);
        }

        foreach ($deleteIds as $id) {
            SQLTest::delete($id);
        }
        $this->redirect('/settings/sql');
    }

    public function sql_addAction()
    {
        $this->minPermission(Auth::RIGHT_READWRITE);
        $this->add('menu', 'settingssql');

        $test = ($this->hasSavedForm('SQLTest')) ? $this->formGet() : new SQLTest();
        $this->load('settings/addsql');
        $this->render(array('devices' => Helper::getDevices($test->sub_dev_type), 'sql' => $test));
    }

    public function sql_editAction()
    {
        $this->minPermission(Auth::RIGHT_READWRITE);
        $this->add('menu', 'settingssql');
        $this->testForPresence(array('get' => array('id')));

        $test = new SQLTest(intval($_GET['id']));
        $this->load('settings/editsql');
        $this->render(array('devices' => Helper::getDevices($test->sub_dev_type), 'sql' => $test));
    }

    public function sql_patchAction()
    {
        $this->minPermission(Auth::RIGHT_READWRITE);
        $this->testForPresence(
            array(
                'post' => array(
                    'id',
                    'csrftoken',
                    'name',
                    'host',
                    'user',
                    'password',
                    'query',
                    'device',
                    'columnnumber',
                    'timeout'
                )
            )
        );
        if (!$this->isValidCsrfToken($_POST['csrftoken'])) {
            throw new ForbiddenException();
        }
        if (empty($_POST['id']) || empty($_POST['name']) || empty($_POST['host']) || empty($_POST['user']) || empty($_POST['password']) || empty($_POST['query']) || empty($_POST['device']) || empty($_POST['columnnumber']) || empty($_POST['timeout'])) {
            $this->errors->append('Empty fields are not allowed!');
        }

        if (mb_strlen($_POST['query'] > 255)) {
            $this->errors->append(
                'The SQL query must not be longer than 255 characters, detected: ',
                mb_strlen($_POST['query'])
            );
        }

        if (!is_numeric($_POST['columnnumber'])) {
            $this->errors->append('Column Number must be a positive int');
        } else {
            $_POST['columnnumber'] = abs($_POST['columnnumber']);
        }
        if (!is_numeric($_POST['timeout'])) {
            $this->errors->append('Timeout must be a positive int');
        } else {
            $_POST['timeout'] = abs($_POST['timeout']);
        }

        // more tests omitted....

        $test = new SQLTest(array(
            'id' => $_POST['id'],
            'name' => $_POST['name'],
            'sub_dev_type' => $_POST['device'],
            'password' => $_POST['password'],
            'host' => $_POST['host'],
            'user' => $_POST['user'],
            'query' => $_POST['query'],
            'column_num' => $_POST['columnnumber'],
            'timeout' => $_POST['timeout']
        ));
        if (!$this->hasErrors()) {
            $test->save();
            $this->success->append('The SQL Test ' . $_POST['name'] . ' has been created');
            $this->redirect('/settings/sql');
        } else {
            $this->formSave($test);
            $this->redirect('/settings/sql/edit?id=' . intval($_POST['id']));
        }
    }

    public function sql_createAction()
    {
        $this->minPermission(Auth::RIGHT_READWRITE);
        $this->testForPresence(
            array(
                'post' => array(
                    'csrftoken',
                    'name',
                    'host',
                    'user',
                    'password',
                    'query',
                    'device',
                    'columnnumber',
                    'timeout'
                )
            )
        );
        if (!$this->isValidCsrfToken($_POST['csrftoken'])) {
            throw new ForbiddenException();
        }
        if (empty($_POST['name']) || empty($_POST['host']) || empty($_POST['user']) || empty($_POST['password']) || empty($_POST['query']) || empty($_POST['device']) || empty($_POST['columnnumber']) || empty($_POST['timeout'])) {
            $this->errors->append('Empty fields are not allowed!');
        }

        if (mb_strlen($_POST['query'] > 255)) {
            $this->errors->append(
                'The SQL query must not be longer than 255 characters, detected: ',
                mb_strlen($_POST['query'])
            );
        }

        if (!is_numeric($_POST['columnnumber'])) {
            $this->errors->append('Column Number must be a positive int');
        } else {
            $_POST['columnnumber'] = abs($_POST['columnnumber']);
        }
        if (!is_numeric($_POST['timeout'])) {
            $this->errors->append('Timeout must be a positive int');
        } else {
            $_POST['timeout'] = abs($_POST['timeout']);
        }

        // more tests omitted....

        $test = new SQLTest(array(
            'name' => $_POST['name'],
            'sub_dev_type' => $_POST['device'],
            'password' => $_POST['password'],
            'host' => $_POST['host'],
            'user' => $_POST['user'],
            'query' => $_POST['query'],
            'column_num' => $_POST['columnnumber'],
            'timeout' => $_POST['timeout']
        ));
        if (!$this->hasErrors()) {
            $test->save();
            $this->success->append('The SQL Test ' . $_POST['name'] . ' has been created');
            $this->redirect('/settings/sql');
        } else {
            $this->formSave($test);
            $this->redirect('/settings/sql/add');
        }
    }

    public function usersAction()
    {
        $this->minPermission(Auth::RIGHT_ADMIN);
        $this->add('menu', 'settingsusers');


        $users = getDatabase()
            ->query(
                'SELECT id, user, fullname, IF(disabled = 0, permit, ' . Auth::RIGHT_DISABLED . ') AS permit FROM user ORDER BY user'
            )
            ->fetchAll(\PDO::FETCH_ASSOC);

        $users = $this->mapPermissions($users);

        $this->load('settings/users');
        $this->render(array('users' => $users, 'sumusers' => count($users)));
    }

    private function mapPermissions(array $users)
    {
        $tmp = array();
        foreach ($users as $key => $val) {
            $val['permit'] = $GLOBALS['PERMIT_TYPES'][$val['permit']];
            $tmp[$key] = $val;
        }

        return $tmp;
    }

    public function users_editAction()
    {
        $this->minPermission(Auth::RIGHT_ADMIN);
        $this->add('menu', 'settingsusers');

        $this->testForPresence(array('get' => array('id')));

        $user = new User($_GET['id']);
        $this->load('settings/edituser');

        $values = array(
            'permittypes' => Auth::getPermissionTypes($user->permit),
            'groups' => Auth::getGroups($user->group_id),
            'user' => $user,
            'slideshow' => ($user->slideshow) ? 'checked="checked"' : ''
        );

        $this->render($values);
    }

    public function users_patchAction()
    {
        $this->minPermission(Auth::RIGHT_ADMIN);
        $this->testForPresence(
            array(
                'post' => array(
                    'csrftoken',
                    'username',
                    'prettyname',
                    'password',
                    'password2',
                    'permit',
                    'group',
                    'userid'
                )
            )
        );
        if (!$this->isValidCsrfToken($_POST['csrftoken'])) {
            throw new ForbiddenException();
        }
        if (!empty($_POST['password'])) {
            if (!$_POST['password'] == $_POST['password2']) {
                $this->errors->append('The passwords did not match');
            }
        }

        if (!$this->hasErrors()) {
            $user = new User($_POST['userid']);
            $user->patch(
                array(
                    'user' => $_POST['username'],
                    'fullname' => $_POST['prettyname'],
                    'password' => $_POST['password'],
                    'permit' => $_POST['permit'],
                    'group_id' => $_POST['group'],
                    'slideshow' => isset($_POST['slideshow']) ? true : false
                )
            );
            $this->success->append('The user ' . $_POST['prettyname'] . ' has been updated');
        }

        $this->redirect('/settings/users');
    }

    public function users_createAction()
    {
        $this->minPermission(Auth::RIGHT_ADMIN);
        $this->testForPresence(
            array(
                'post' => array(
                    'csrftoken',
                    'username',
                    'prettyname',
                    'password',
                    'password2',
                    'permit',
                    'group',
                    'userid'
                )
            )
        );
        if (!$this->isValidCsrfToken($_POST['csrftoken'])) {
            throw new ForbiddenException();
        }
        if (!Configuration::externalAuth() && empty($_POST['password'])) {
            $this->errors->append('the password must not be empty unless external Auth is active');
        }
        if (!empty($_POST['password'])) {
            if (!$_POST['password'] == $_POST['password2']) {
                $this->errors->append('The passwords did not match');
            }
        }
        if ($this->auth->userExists(trim($_POST['username']))) {
            $this->errors->append(
                'There already exists an user with the name ' . $_POST['username']
            );
        }

        if (!$this->hasErrors()) {
            $user = new User();
            $user->create(
                array(
                    'user' => $_POST['username'],
                    'fullname' => $_POST['prettyname'],
                    'password' => $_POST['password'],
                    'permit' => $_POST['permit'],
                    'group_id' => $_POST['group'],
                    'slideshow' => isset($_POST['slideshow']) ? true : false
                )
            );
            $this->success->append('The user ' . $_POST['prettyname'] . ' has been created');
        }

        $this->redirect('/settings/users');
    }

    public function users_addAction()
    {
        $this->minPermission(Auth::RIGHT_ADMIN);
        $this->add('menu', 'settingsusers');


        $this->load('settings/adduser');
        $this->render(
            array('permittypes' => Auth::getPermissionTypes(), 'groups' => Auth::getGroups())
        );
    }

    public function users_deleteAction()
    {
        $this->minPermission(Auth::RIGHT_ADMIN);
        $this->testForPresence(
            array(
                'post' => array('csrftoken')
            )
        );

        if (!$this->isValidCsrfToken($_POST['csrftoken'])) {
            throw new ForbiddenException();
        }

        $deleteIds = array();
        if (isset($_GET['id'])) {
            $deleteIds[] = $_GET['id'];
        } elseif (isset($_POST['delids'])) {
            $deleteIds = explode(',', $_POST['delids']);
        }

        foreach ($deleteIds as $id) {
            $this->auth->deleteUser($id);
        }
        $this->redirect('/settings/users');
    }

}
