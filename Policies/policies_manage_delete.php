<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

use Gibbon\Forms\Prefab\DeleteForm;

//Module includes
include './modules/Policies/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Policies/policies_manage_delete.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    //Proceed!
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/policies_manage.php'>Manage Policies</a> > </div><div class='trailEnd'>Delete Policy</div>";
    echo '</div>';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $policiesPolicyID = $_GET['policiesPolicyID'];
    if ($policiesPolicyID == '') { echo "<div class='error'>";
        echo 'You have not specified a policy.';
        echo '</div>';
    } else {
        try {
            $data = array('policiesPolicyID' => $policiesPolicyID);
            $sql = 'SELECT * FROM policiesPolicy WHERE policiesPolicyID=:policiesPolicyID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo 'The selected policy does not exist.';
            echo '</div>';
        } else {
            //Let's go!
            $row = $result->fetch();

            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL']."/modules/Policies/policies_manage_deleteProcess.php?policiesPolicyID=$policiesPolicyID&search=".$_GET['search']);
            echo $form->getOutput();
        }
    }
}
