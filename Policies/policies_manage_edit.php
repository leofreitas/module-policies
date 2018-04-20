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

use Gibbon\Forms\Form;

//Module includes
include './modules/Policies/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Policies/policies_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    //Proceed!
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/policies_manage.php'>Manage Policies</a> > </div><div class='trailEnd'>Edit Policy</div>";
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
            $values = $result->fetch();

            if ($_GET['search'] != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Policies/policies_manage.php&search='.$_GET['search']."'>Back to Search Results</a>";
                echo '</div>';
            }

            $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/Policies/policies_manage_editProcess.php?policiesPolicyID='.$policiesPolicyID.'&search='.$_GET['search']);
        
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
                $row->addLabel('scope', 'Scope');
                $row->addTextField('scope')->readonly();

            if ($values['scope'] == 'Department') {
                $sql = "SELECT gibbonDepartmentID as value, name FROM gibbonDepartment ORDER BY name";
                $row = $form->addRow();
                    $row->addLabel('gibbonDepartmentID', __('Department'));
                    $row->addSelect('gibbonDepartmentID')->fromQuery($pdo, $sql)->isRequired()->placeholder()->readonly();
            }

            $row = $form->addRow();
                $row->addLabel('name', __('Name'));
                $row->addTextField('name')->maxLength(100)->isRequired();

            $row = $form->addRow();
                $row->addLabel('nameShort', __('Short Name'));
                $row->addTextField('nameShort')->maxLength(14)->isRequired();

            $row = $form->addRow();
                $row->addLabel('active', __('Active'));
                $row->addYesNo('active')->isRequired();

            $sql = "SELECT DISTINCT category FROM policiesPolicy ORDER BY category";
            $result = $pdo->executeQuery(array(), $sql);
            $categories = ($result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_COLUMN, 0) : array();

            $row = $form->addRow();
                $row->addLabel('category', __('Category'));
                $row->addTextField('category')->maxLength(100)->autocomplete($categories);

            $row = $form->addRow();
                $row->addLabel('description', __('Description'));
                $row->addTextArea('description')->setRows(5);

            $row = $form->addRow();
                $row->addLabel('type', __('Type'));
                $row->addTextField('type')->readonly();

            if ($values['type'] == 'File') {
                $row = $form->addRow();
                    $row->addLabel('file', __('Policy File'));
                    $row->addFileUpload('file')->isRequired()->setAttachment('attachment', $_SESSION[$guid]['absoluteURL'], $values['location']);
            } else if ($values['type'] == 'Link') {
                $row = $form->addRow();
                    $row->addLabel('link', __('Policy Link'));
                    $row->addURL('link')->maxLength(255)->isRequired()->setValue($values['location']);
            }

            $values['roleCategories'] = array();
            if ($values['staff'] == 'Y') $values['roleCategories'][] = "staff";
            if ($values['student'] == 'Y') $values['roleCategories'][] = "student";
            if ($values['parent'] == 'Y') $values['roleCategories'][] = "parent";

            $sql = "SELECT DISTINCT LOWER(category) as value, category as name FROM gibbonRole";
            $row = $form->addRow();
                $row->addLabel('roleCategories', __('Audience By Role Category'))->description(__('User role categories who should have view access.'));
                $row->addCheckbox('roleCategories')->fromQuery($pdo, $sql);

            $values['gibbonRoleIDList'] = explode(',', $values['gibbonRoleIDList']);
            $sql = "SELECT gibbonRoleID as value, name FROM gibbonRole ORDER BY name";
            $row = $form->addRow();
                $row->addLabel('gibbonRoleIDList', __('Audience By Role'))->description(__('User role groups who should have view access.'));
                $row->addCheckbox('gibbonRoleIDList')->fromQuery($pdo, $sql);

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();
        }
    }
}
