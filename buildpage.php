<?php include 'dbaseconn.php'; ?>
<?php include 'functions.php'; ?>

<?php //this file is used to handle all of the ajax requests from the website.  It first includes the functions file and also the database connections file.
    ?>

<?php // buildpage.php
    
    //when page is run in ajax call, this code evaluates
    
    if (isset($_POST['calltype'])){
        
        $calltype = SanitizeString($_POST['calltype']);
        //if the variable call type is set in the ajax call, sanitize it
        
        if($calltype == "jobincrement"){
            //if the call type is job increment, new data is loaded for the particular job.
            if(isset($_POST['jobid'])){
                //if the jobid is set, sanitize the strings for user and jobid
                $jobid = SanitizeString($_POST['jobid']);
                $user = SanitizeString($_POST['user']);
                
                $page = array();
                $page = rebuildPage($user,$jobid);
                //call the function to get new data for the page
                
                echo json_encode($page);
                //encode the data as a json variable, which is a cool name by the way, and then echo it to the caller
            }
        }
        elseif($calltype == "pagechange"){
            //if tye type is page change, a nav bar click has happened and a new page is generated
            if(isset($_POST['pagetype'])){
                $pagetype = SanitizeString($_POST['pagetype']);
                $user = SanitizeString($_POST['user']);
                if($pagetype == "Job"){
                   
                    $dataarray = array();
                    $dataarray = getJobPageData($user);
                    $pagedata = $dataarray['pagedata'];
                    $taskdata = $dataarray['taskdata'];
                    $taskaddlinfo = $dataarray['taskaddlinfo'];
                    $morecontent = $dataarray['morecontent'];
                    $page = buildJobPage($pagedata,$taskdata,$taskaddlinfo,$morecontent);
                }
                elseif($pagetype == "Education"){
                    $edupagedata = getEduData($user);
                    $page = buildEduPageCards($edupagedata);
                }
                else{
                    echo "ELSE";
                }
                echo $page;
            }
        }
        else{
            echo "ELSE EXECUTED";
        }
        
    }
    
?>

<?php function SanitizeString($var){
    //this function removes malicious code that could be injected into the site
    $var = strip_tags($var);
    $var = htmlentities($var);
    return stripslashes($var);
} ?>

<?php function rebuildPage($user,$jobid){
    //this function generates the data used to change jobs on the job page

    //first set all of the tables used by the database queries
    $startpagetype = "Job";
    $startpagetable = "job_education";
    $startpagemorecontenttable = "job_education_more_info";
    $startpagetasktable = "job_education_task";
    $startpagetaskaddlinfotable = "job_education_task_more_info";
    
    //get the data for the job overview section
    $pagedata = getData($user,$startpagetable,$startpagetype,$jobid);
    //get the data for the more content of the job overview section
    $morecontent = getMoreContentData($startpagemorecontenttable,$jobid);
    //get data for the carousel
    $taskdata = getTaskData($startpagetasktable, $jobid);
    //get data for the hideable content section of the carousel
    $taskaddlinfo = getTaskAdditionalInfoData($startpagetaskaddlinfotable,$jobid);
    
    $htmlforpage = array();
    //build the html for the new page and return it.
    $htmlforpage = buildJobPageAjax($pagedata,$taskdata,$taskaddlinfo,$morecontent,$jobid);

    return $htmlforpage;
    
    
} ?>

<?php function getData($user,$startpagetable,$startpagetype,$jobid){

    //make the sql query based on the inputs for tables and return the results.
    $startpagequery = "SELECT * FROM ".$startpagetable." WHERE `index` = ".$jobid;
    $startpageresults = queryMysql($startpagequery);
    return $startpageresults;
} ?>

<?php function getMoreContentData($startpagemorecontenttable, $jobid){
    
    //make sql query to get more content data
    $startpagemorecontentquery = "SELECT * FROM ".$startpagemorecontenttable." WHERE key_from_job_edu = ".$jobid;
    $startpagemorecontentresults = queryMysql($startpagemorecontentquery);

    return $startpagemorecontentresults;
} ?>

<?php function getTaskData($startpagetasktable, $jobid){
    
    //build data for the carousel, this should be changed to remove the key_from _job_edu because it could change 
    //according to the type of page
    $startpagetaskquery = "SELECT * FROM ".$startpagetasktable." WHERE key_from_job_edu = ".$jobid;
    $startpagetaskresults = queryMysql($startpagetaskquery);
    //$startpagetaskdata = mysql_fetch_assoc($startpagetaskresults);
    
    return $startpagetaskresults;

} ?>

<?php function getTaskAdditionalInfoData($startpagetaskaddlinfotable,$jobid){
    
    //this gets the data for the hideable content in the box below the carousel.  Right now it doesn't do anything.
    //The get the key=0 could be causing a problem...not sure
    $startpagetaskaddlinfoquery = "SELECT * FROM ".$startpagetaskaddlinfotable." WHERE key_from_job_edu_task = 0";
    $startpagetaskaddlinforesults = queryMysql($startpagetaskaddlinfoquery);
    
    return $startpagetaskaddlinforesults;
    
} ?>

<?php function getEduData($user){
    //make the sql query based on the inputs for tables and return the results
    $edupagequery = "SELECT * FROM "."job_education"." WHERE type_of_record = 'Education' AND username='".$user."'";
    $edupageresults = queryMysql($edupagequery);
    
    return $edupageresults;
} ?>
