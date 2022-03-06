<?php

// require the parent class
require "CvSectionController.php";

class EducationSectionController extends CvSectionController{
    // save an education
    public function SaveData(){
        // this array will be sent as a response to the client
        $response_array["action_completed"] = false;
        $response_array["error"] = "";

        if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["degree"]) && isset($_POST["field"]) && isset($_POST["school_name"]) && isset($_POST["education_start_date"]) && isset($_POST["education_end_date"])){
            try{
                if($this->sectionModel->auth->isLoggedIn()){
                    // sanitize the user's input
                    $newDegree = new Input($_POST["degree"]);
                    $newDegree->Sanitize();
                    $newField = new Input($_POST["field"]);
                    $newField->Sanitize();
                    $newSchoolName = new Input($_POST["school_name"]);
                    $newSchoolName->Sanitize();
                    $newStartDate = new Input($_POST["education_start_date"]);
                    $newStartDate->Sanitize();
                    $newEndDate = new Input($_POST["education_end_date"]);
                    $newEndDate->Sanitize();

                    // check to see if the user already saved this education
                    $oldDegree = new Input("");
                    $oldField = new Input("");
                    $oldSchoolName = new Input("");
                    if(isset($_POST["old_degree"]) && isset($_POST["old_field"]) && isset($_POST["old_school_name"])){
                        // sanitize the input
                        $oldDegree->value = $_POST["old_degree"];
                        $oldDegree->Sanitize();
                        $oldField->value = $_POST["old_field"];
                        $oldField->Sanitize();
                        $oldSchoolName->value = $_POST["old_school_name"];
                        $oldSchoolName->Sanitize();
                    } else{
                        $oldDegree->value = null;
                        $oldField->value = null;
                        $oldSchoolName->value = null;
                    }

                    // check if the dates are valid
                    if(date("Y-m-d", strtotime($newStartDate->value)) == date($newStartDate->value) && date("Y-m-d", strtotime($newEndDate->value)) == date($newEndDate->value)){
                        // get the user id
                        $userId = $this->sectionModel->auth->getUserId();

                        // save the education
                        $this->sectionModel->InsertData(array("user_id" => $userId, "new_degree" => $newDegree->value, "new_field" => $newField->value, "new_school_name" => $newSchoolName->value, "new_start_date" => $newStartDate->value, "new_end_date" => $newEndDate->value, "old_degree" => $oldDegree->value, "old_field" => $oldField->value, "old_school_name" => $oldSchoolName->value));

                        // indicate that the action has completed
                        $response_array["action_completed"] = true;
                    } else{
                        $response_array["error"] = "Invalid dates";
                    }
                } else{
                    $response_array["logged_in"] = false;
                }
            } catch (Exception $e) {
                $response_array["error"] = $e->getMessage();
            }
        }

        echo json_encode($response_array);
    }

    // delete an education
    public function DeleteData(){
        // this array will be sent as a response to the client
        $response_array["action_completed"] = false;
        $response_array["error"] = "";
        
        if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["old_degree"]) && isset($_POST["old_field"]) && isset($_POST["old_school_name"])){
            try{
                if($this->sectionModel->auth->isLoggedIn()){
                    // indicate that the user is logged in
                    $response_array["logged_in"] = true;

                    // sanitize the input
                    $oldDegree = new Input($_POST["old_degree"]);
                    $oldDegree->Sanitize();
                    $oldField = new Input($_POST["old_field"]);
                    $oldField->Sanitize();
                    $oldSchoolName = new Input($_POST["old_school_name"]);
                    $oldSchoolName->Sanitize();

                    // get the user id
                    $userId = $this->sectionModel->auth->getUserId();

                    // delete the education
                    $this->sectionModel->DeleteData(array("user_id" => $userId, "old_degree" => $oldDegree->value, "old_field" => $oldField->value, "old_school_name" => $oldSchoolName->value));

                    // indicate that the action has completed
                    $response_array["action_completed"] = true;
                } else{
                    $response_array["logged_in"] = false;
                }
            } catch (Exception $e) {
                $response_array["error"] = $e->getMessage();
            }
        }

        echo json_encode($response_array);
    }

    // add another education subsection
    public function AddSubsecToSec(){
        // this array will be sent as a response to the client
        $response_array["action_completed"] = false;
        $response_array["error"] = "";
        $response_array["new_subsec_html"] = "";
        
        if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["subsecs_in_section"])){
            try{

                // sanitize the user's input
                $educationsNumber = new Input($_POST["subsecs_in_section"]);
                $educationsNumber->Sanitize();
                if(preg_match("/^(0|[1-9]+[0-9]*)$/", $educationsNumber->value)){
                    $response_array["new_subsec_html"] = "
                    <div class='education' id='education_" . strval($educationsNumber->value + 1) . "'>
                        <form id='save_education_" . strval($educationsNumber->value + 1) . "_section_form'>
                            <input type='text' name='degree' placeholder='Degree'>
                            <input type='text' name='field' placeholder='Field'>
                            <input type='text' name='school_name' placeholder='School Name'>
                            <input type='date' class='form-control' name='education_start_date' placeholder=''>
                            <input type='date' class='form-control' name='education_end_date' placeholder=''>
                            <button type='submit' onclick=" . '"'. "ModifySection('education_" . strval($educationsNumber->value + 1) . "', 'save', 'EducationSectionController')" . '"'. ">Save Education</button>
                        </form>
                        <form id='delete_education_" . strval($educationsNumber->value + 1) . "_section_form'>
                            <button type='submit' onclick=" . '"' . "ModifySection('education_" . strval($educationsNumber->value + 1) . "', 'delete', 'EducationSectionController')" . '"' . ">Delete Education</button>
                        </form>
                    </div>";

                    // indicate that the action has completed
                    $response_array["action_completed"] = true;
                } else{
                    $response_array["error"] = "Invalid data";
                }
            } catch (Exception $e) {
                $response_array["error"] = $e->getMessage();
            }
        }

        echo json_encode($response_array);
    }
}

// a request has been sent from a view
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])){
    // require the input sanitizer
    require "InputController.php";

    // retrieve the action
    $action = new Input($_POST["action"]);
    $action->Sanitize();

    // perform the action
    $account = new EducationSectionController("EducationSectionModel");
    $account->performAction($action->value);
}

?>