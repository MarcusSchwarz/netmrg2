<?php
/**
 * Auth.php
 * Functions used for authentication in NetMRG
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

namespace Netmrg;


/**
 * Class Auth
 * @package Netmrg
 */
class Auth
{

    const RIGHT_DISABLED = -1;
    const RIGHT_SINGLEVIEWONLY = 0;
    const RIGHT_READALL = 1;
    const RIGHT_READWRITE = 2;
    const RIGHT_ADMIN = 3;

    /**
     * @var Database|null
     */
    private $db = null;
    /**
     * @var Session|null
     */
    private $session = null;

    /**
     * @var bool|null
     */
    private $isLoggedIn = null;

    /**
     * @param Session|null $session
     * @param Database|null $databaseHandler
     */
    public function __construct(Session $session, Database $databaseHandler = null)
    {
        if (!empty($databaseHandler)) {
            $this->db = $databaseHandler;
        } else {
            // old school way
            $this->db = getDatabase();
        }
        $this->session = $session;

        return true;
    }

    /**
     * viewCheckAuthRedirect($object_id, $object_type)
     * called from the 'view.php' page
     * checks that the user is allowed to see this page
     * and redirects if they are not
     */
    public function viewCheckAuthRedirect($object_id, $object_type)
    {
        if (!$this->viewCheckAuth($object_id, $object_type)) {
            $this->redirectErrorDenied($_SERVER['REQUEST_URI']);
        }
    }

    // aka check_user(session user)

    /**
     * viewCheckAuth($object_id, $object_type)
     * called from the 'view.php' page
     * checks that the user is allowed to see this page
     */
    public function viewCheckAuth($object_id, $object_type)
    {
        global $PERMIT;

        $this->userHasAtLeastPermissionLevel($GLOBALS['PERMIT']["SingleViewOnly"]);

        // the groups this object_id is in
        $object_id_groups = GetGroups($object_type, $object_id);

        if (
            !in_array($this->session->get('group_id'), $object_id_groups)
            &&
            $this->session->get('permit') == $PERMIT["SingleViewOnly"]
        ) {
            return false;
        } // end if allowed group id is not in this objects groups and we're SVO

        return true;
    }

    // aka check_user(global default map user)

    /**
     * check_auth($level)
     * checks the logged in user's auth level to be sure they have
     * at least auth level $level.  If not, send them away
     */
    public function userHasAtLeastPermissionLevel($level)
    {
        if (!$this->userIsLoggedIn()) {
            $this->session->set('redir', $_SERVER['REQUEST_URI']);
            if ($GLOBALS["netmrg"]["externalAuth"]) {
                header("Location: {$GLOBALS['netmrg']['webroot']}/index/login");
                exit;
            }
            $this->redirectErrorInvalid();
        } // if they don't have enough permissions
        else {
            if ($this->session->get('permit') < $level) {
                $this->redirectErrorDenied();
            }
        }

        return true;
    }

    public function getUsername($uid = null)
    {
        if (is_null($uid)) {
            return $this->session->get('prettyname');
        }

        $tmp = $this->db->query('SELECT user FROM user WHERE id = ' . intval($uid));

        if ($tmp->rowCount() == 1) {
            return $tmp->fetchColumn();
        }
        throw new Netmrg404Exception();


    }

    public function resetLoggedInState()
    {
        $this->isLoggedIn = null;
    }

    /**
     * IsLoggedIn();
     * verifies a username and password in the session
     * against what's in the database
     * and that the user isn't spoofing their ip
     * and that they haven't been logged in too long
     */
    public function userIsLoggedIn()
    {
        if (!is_null($this->isLoggedIn)) {
            return $this->isLoggedIn;
        }
        if ((
                ($GLOBALS["netmrg"]["externalAuth"] && ($this->userExists(
                        ) || $this->defaultUserExists()))
                ||
                (!$GLOBALS["netmrg"]["externalAuth"] && $this->userHasCorrectPassword())
            )
            && $this->session->get('remote_addr') == $_SERVER["REMOTE_ADDR"]
            && time() - $this->session->get(
                'accessTime'
            ) <= $GLOBALS["netmrg"]["authTimeout"]
        ) {
            $this->isLoggedIn = true;
        } else {
            $this->isLoggedIn = false;
        }
        return $this->isLoggedIn;
    }

    /**
     * check_user($user)
     * verifies a username (for external auth)
     *   $user = username

     */
    public function userExists($user = null)
    {
        $user = (!empty($user)) ? $user : $this->session->get('username');

        return $this->userExistsInDatabase($user);
    }

    /**
     * @param $user
     * @return bool
     */
    private function userExistsInDatabase($user)
    {
        $s = $this->db->prepare('SELECT 1 FROM user WHERE user = :user');
        $s->bindValue(':user', $user);
        $s->execute();
        $result = $s->fetchColumn();

        return (1 == intval($result)) ? true : false;
    }

    /**
     * @return bool
     */
    public function defaultUserExists()
    {
        return $this->userExistsInDatabase(
            $GLOBALS['netmrg']['defaultMapUser']
        );
    }

    /**
     * @param null $username
     * @param null $password
     * @return bool
     */
    public function userHasCorrectPassword($username = null, $password = null)
    {
        $username = (!empty($username)) ? $username : $this->session->get('username');
        $password = (!empty($password)) ? $password : $this->session->get('password');

        // todo we should try to get the password out of the session
        return $this->check_user_pass($username, $password);
    }

    /**
     * check_user_pass($user, $pass);
     * verifies a username and password agains what's in the database
     *   $user = username
     *   $pass = password
     */
    private function check_user_pass($user, $pass)
    {
        // todo should be made more comfortable...
        $currentGenerationHash = 2;

        $s = $this->db->prepare('SELECT 1 FROM user WHERE user = :user AND pass = :pass');
        $s->bindValue(':user', $user);
        $s->bindValue(':pass', $this->generate_password_hash($pass, $currentGenerationHash));
        $s->execute();
        $result = $s->fetchColumn();

        if (1 == intval($result)) {
            return true;
        }

        // test against old type password hashes
        $s->bindValue(':user', $user);
        $s->bindValue(':pass', $this->generate_password_hash($pass, 1));
        $s->execute();
        $result = $s->fetchColumn();

        if (1 == intval($result)) {
            // update old password hash
            $s = $this->db->prepare('UPDATE user SET pass = :pass WHERE user = :user');
            $s->bindValue(':user', $user);
            $s->bindValue(':pass', $this->generate_password_hash($pass, $currentGenerationHash));
            $s->execute();

            return true;
        }

        return false;
    }

    /**
     * @param $pass
     * @param $generation
     * @return string
     * @throws
     */
    public function generate_password_hash($pass, $generation = null)
    {
        $generation = (!empty($generation)) ? $generation : 2;
        if ($generation == 1) {
            return md5($pass);
        }
        if ($generation == 2) {
            for ($i = 0; $i < 10240; $i++) {
                $pass = hash('sha512', $pass . HASHING_SECRET);
            }

            return $pass;
        }
        throw new NetmrgException('this password hash is not invented yet');
    }

    /**
     *
     */
    private function redirectErrorInvalid()
    {
        $this->redirect('invalid');
    }

    /**
     * @param $action
     */
    private function redirect($action)
    {
        header("Location: {$GLOBALS['netmrg']['webroot']}/error.php?action={$action}");
        exit;
    }

    /**
     * @param string $redirectTo
     */
    public function redirectErrorDenied($redirectTo = '')
    {
        if (!empty($redirectTo)) {
            $this->session->set('redir', $redirectTo);
        }
        $this->redirect('denied');
    }

    /**
     * EncloseGraphCheckAuth()
     * makes sure that the logged in user can view a graph
     * type = template, custom, mon, tinymon
     * id = id of item

     */
    public function EncloseGraphCheckAuth($type, $id)
    {
        // todo this is a special case for enclose_graph.php, maybe it should be refactored
        global $PERMIT;
        $this->userHasAtLeastPermissionLevel($GLOBALS['PERMIT']["SingleViewOnly"]);

        // the groups this object_id is in
        $object_id_groups = array();

        switch ($type) {
            case "mon" :
            case "tinymon" :
                $object_id_groups = GetGroups("monitor", $id);
                break;

            case "template" :
                $object_id_groups = GetGroups("subdevice", $id);
                break;

            case "custom" :
                $object_id_groups = GetGroups("customgraph", $id);
                break;
        }

        if (
            !in_array($this->session->get('group_id'), $object_id_groups)
            &&
            $this->session->get('permit') == $PERMIT["SingleViewOnly"]
        ) {
            $this->redirectErrorDenied($_SERVER['REQUEST_URI']);
        }
    }

    /**
     * GraphCheckAuth()
     * makes sure that the logged in user can view a graph
     * type = template, custom, mon, tinymon
     * id = id of item

     */
    public function GraphCheckAuth($type, $id)
    {
        //todo this is a special case für get_graph.php, maybe we should refactor it
        global $PERMIT;
        $this->userHasAtLeastPermissionLevel($GLOBALS['PERMIT']["SingleViewOnly"]);

        // the groups this object_id is in
        $object_id_groups = array();

        switch ($type) {
            case "mon" :
            case "tinymon" :
                $object_id_groups = GetGroups("monitor", $id);
                break;

            case "template" :
            case "template_item" :
                $object_id_groups = GetGroups("subdevice", $id);
                break;

            case "custom" :
            case "custom_item" :
                $object_id_groups = GetGroups("customgraph", $id);
                break;
        }

        if (
            !in_array($this->session->get('group_id'), $object_id_groups)
            &&
            $this->session->get('permit') == $PERMIT["SingleViewOnly"]
        ) {
            readfile(
                $GLOBALS["netmrg"]["fileroot"] . "/webfiles/img/access_denied.png"
            );
            exit;
        }
    }

    /**
     * ResetAuth()
     * reset authentication variables
     */
    public function logoutAndResetSession()
    {
        $this->session->reset();
    }

    /**
     * @param null $user
     * @return string
     */
    public function getUsersPermissionLevel($user = null)
    {
        $user = (!empty($user)) ? $user : $this->session->get('username');
        global $PERMIT;

        if ($this->userIsLoggedIn()) {
            $s = $this->db->prepare(
                'SELECT IF(disabled = 0, permit, :permit) AS permit FROM user WHERE user = :user'
            );
            $s->bindValue(':permit', $PERMIT['Disabled']);
            $s->bindValue(':user', $user);
            $s->execute();

            return $s->fetchColumn();
        }

        return $PERMIT['Disabled'];
    }

    public function updatePassword($uid, $newpass)
    {
        $s = getDatabase()->prepare('UPDATE user SET pass = :pass WHERE id = :id');
        $s->bindValue(':pass', $this->generate_password_hash($newpass));
        $s->bindValue(':id', $uid);
        $s->execute();
        $this->session->set('password', $newpass);
    }

    /**
     * GetUserID()
     * gets the user id of the logged in user
     */
    public function getUserID()
    {
        if ($this->userIsLoggedIn()) {
            $s = $this->db->prepare('SELECT id FROM user WHERE user= :user');
            $s->bindValue(':user', $this->session->get('username'));
            $s->execute();

            return $s->fetchColumn();
        }

        return false;
    }

    /**
     * @param string $user
     * @return bool|string
     */
    public function getUserGroupId($user = '')
    {
        if (empty($user)) {
            $user = $this->session->get('username');
        }

        if ($this->userIsLoggedIn()) {
            $s = $this->db->prepare('SELECT group_id FROM user WHERE user = :user');
            $s->bindValue(':user', $user);
            $s->execute();

            return $s->fetchColumn();
        }

        return false;
    }

    /**
     * view_redirect()
     * redirects the logged in user to the 'view' page
     * if they only have 'single view' privileges or they
     * weren't on their way to somewhere else
     */
    public function view_redirect()
    {
        $redir = $this->session->get(
            'redir'
        ); // 5.3 does not allow empty(func())
        if (empty($redir) || ($this->session->get('permit') == 0)) {
            header("Location: {$GLOBALS['netmrg']['webroot']}/device_tree.php");
            exit;
        } else {
            $this->session->set('redir', null);
            header("Location: {$redir}");
            exit;
        }
    }
}
