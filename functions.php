<?php

function buildDefaultPage($userToGet) {
    //This function is called by the page at startup to generate the data
    $user = $userToGet;
    $typeofpage = "experience";
    $returnHtml = "";

    //make SQL query to get data for current user
    $userquery = "SELECT * FROM users WHERE username = '" . $user . "'";
    $userresults = queryMysql($userquery);
    $userdata = mysql_fetch_assoc($userresults);

    //this is used to choose what type of page the user wants as their opening page
    $startpagetype = $userdata["start_page_type"];

    //this builds the navbar based on what pages the user has defined.  It should be changed so that it looks to a 
    //database to get the types of pages that can be built instead of just having hard coded tables
    $navtypesquery = "(SELECT DISTINCT type_of_record FROM job_education WHERE username = '" . $user . "') UNION (SELECT 
        DISTINCT type_of_record FROM skill_hob_ref WHERE username = '" . $user . "')";
    $navtypesresults = queryMysql($navtypesquery);

    //right now, I am just looking to a column in the database to find out what type of page to build for the start 
    //page.  I should create a new table that has the types of pages that can be built.  This would allow for more 
    //automated page building.
    if ($startpagetype == "Job" || "Education") {
        $startpagetable = "job_education";
        $orderby = "start_date DESC";
        $startpagetasktable = "job_education_task";
        $startpagetaskaddlinfotable = "job_education_task_more_info";
        $startpagemorecontenttable = "job_education_more_info";
    } else {
        $startpagetable = "skill_hob_ref";
        $orderby = "";
        $startpagetasktable = "skill_hob_ref_task";
        $startpagetaskaddlinfotable = "skill_hob_ref_task_more_info";
        $startpagemorecontenttable = "skill_hob_ref_more_info";
    }

    //get data for the start page
    $startpagequery = "SELECT * FROM " . $startpagetable . " WHERE username = '" . $user . "' AND type_of_record = '"
            . $startpagetype . "' ORDER BY " . $orderby;
    $startpageresults = queryMysql($startpagequery);

    //load the data as an associative array to allow for getting columns by name and set the data seek index to 0 to 
    //return it to the beginning of the array
    $startpagedata = mysql_fetch_assoc($startpageresults);
    $startpagefirstjobindex = $startpagedata["index"];
    $bool = mysql_data_seek($startpageresults, 0);
    //using this function sets the position of the array back to the first element.  This is done because each time the 
    //array is accessed for any contained data, it automatically increments to the next row.
    //get data for the carousel on the start page
    $startpagetaskquery = "SELECT * FROM " . $startpagetasktable . " WHERE key_from_job_edu = " . $startpagefirstjobindex;
    $startpagetaskresults = queryMysql($startpagetaskquery);
    $startpagetaskdata = mysql_fetch_assoc($startpagetaskresults);

    //get data for the job overview's additional information section
    $startpagemorecontentquery = "SELECT * FROM " . $startpagemorecontenttable . " WHERE key_from_job_edu = "
            . $startpagefirstjobindex;
    $startpagemorecontentresults = queryMysql($startpagemorecontentquery);
    //$startpagemorecontentdata = mysql_fetch_assoc($startpagemorecontentdata);
    //get data for the hideable content in the box below the carousel
    $startpagetaskaddlinfoquery = "SELECT * FROM " . $startpagetaskaddlinfotable . " WHERE key_from_job_edu_task = "
            . $startpagetaskdata["index"];
    $startpagetaskaddlinforesults = queryMysql($startpagetaskaddlinfoquery);
    $bool = mysql_data_seek($startpagetaskresults, 0);

    //create the html in 2 main sections, the upper and lower sections

    $upperContainer = "<div id='uppercontainer' class='uppercontainer'>" . getProfileInfo($userdata, $startpagedata) .
            "<div id='navbar'>" . buildNavBar($navtypesresults) . "</div></div>";
    $lowerContainer = "<div id='lowercontainer' class='lowercontainer'>" . buildLowerContainer($startpageresults, $startpagetype, $startpagetaskresults, $startpagetaskaddlinforesults, $startpagemorecontentresults) . "</div>";
    $bottomContainer = "<div id='bottomcontainer'> </div>";
    //return the joined html to the caller
    $returnHtml = $upperContainer . $lowerContainer . $bottomContainer;
    return $returnHtml;
}
?>

<?php

function getProfileInfo($userdata, $startpagedata) {
    //this function gets the user information for the top section of the main profile

    $topsection = "<div id='mainphoto'><img src='" . $userdata["picture_path"] . "'></div>
            <div id='toptext' id='profilepicture'>
                <h1>" . $userdata["first_name"] . " " . $userdata["last_name"] . "</h1>
                <h2>" . $startpagedata["title"] . " at " . $startpagedata["record_name"] . "</h2>
                <p>" . $userdata["statement"] . "</p>
                <a href=''><h3>Contact Me</h3></a>
            </div>";

    return $topsection;
}
?>

<?php

function buildNavBar($navtypesresults) {

    //This function is used to build the nav bar in the top section.  It looks to both the job education table and the 
    //skills hobbies and references table and finds the number of unique record types for a user and uses this to see 
    //which data the user has defined.

    $numrows = mysql_num_rows($navtypesresults);

    for ($i = 0; $i < $numrows; ++$i) {
        if ($i == 0) { //for i=0 give the class "hornavselected"
            //fetch each row associatively and then build each div so that it is clickable with the getPage function.
            $row = mysql_fetch_assoc($navtypesresults);
            $navbar.= "<div class='hornav  whitetransparency' onclick='getPage(" . "\"" . $row["type_of_record"] . "\"" . ")'>" .
                    $row["type_of_record"] . "</div>";
        } else {
            //fetch each row associatively and then build each div so that it is clickable with the getPage function.
            $row = mysql_fetch_assoc($navtypesresults);
            $navbar.= "<div class='hornav darktransparency' onclick='getPage(" . "\"" . $row["type_of_record"] . "\"" . ")'>" .
                    $row["type_of_record"] . "</div>";
        }
    }

    return $navbar;
}
?>

<?php

function buildLowerContainer($pagedata, $typeofpage, $taskdata, $taskaddlinfo, $morecontent) {

    //this function contains an if statement that is used to choose the type of page build.  Right now only the job page
    // is defined as the startup page, but others will be built as needed.

    $returnHtml = "";

    if ($typeofpage == "Job") {
        $returnHtml = buildJobPage($pagedata, $taskdata, $taskaddlinfo, $morecontent);
    }

    return $returnHtml;
}
?>

<?php

function buildJobPage($pagedata, $taskdata, $taskaddlinfo, $morecontent) {

    //this function builds the job page.  The page currently consists of 3 columns, the left nav column, the center 
    //overview column and the right carousel column.

    $lowerContainerHtml = "";

    $lowerContainerHtml = "<div class='timeline'>" . "Timeline would go here" . "</div>";
    $leftcolumn = "<div class='jobnavcolumn'>" . buildJobNav($pagedata) . "</div>";
    $centercolumn = "<div class='joboverview'><div id='borderjoboverview' class='borderjoboverview'>" .
            buildJobOverview($pagedata, $morecontent, 0) . "</div></div>";
    $rightcolumn = "<div id='jobtaskinfo' class='jobtaskinfo'>" . buildCarousel($taskdata) .
            buildTaskSpecificInfo($taskdata, $taskaddlinfo) . "</div>";
    $lowerContainerHtml.=$leftcolumn . $centercolumn . $rightcolumn;

    return $lowerContainerHtml;
}
?>

<?php

function buildJobPageAjax($pagedata, $taskdata, $taskaddlinfo, $morecontent, $jobid) {

    //this is the function used by ajax calls to rebuild the right and center columns of the job page.  It is 
    //essentially the same as buildJobPage function, but it doesn't build the left column because this isn't 
    //necessary for ajax.  Also, it puts the data into an array so that this can be converted to a json variable 
    //(which is again a great name)

    $centercolumn = buildJobOverview($pagedata, $morecontent, $jobid);
    $rightcolumn = buildCarousel($taskdata) . buildTaskSpecificInfo($taskdata, $taskaddlinfo);

    $returnarray = array('centercol' => $centercolumn, 'rightcol' => $rightcolumn);

    return $returnarray;
}
?>

<?php

function buildJobNav($pagedata) {

    //build the left navigation column with the data passed into the function

    $returnNav = "";
    $numrows = mysql_num_rows($pagedata);

    //loop through each row and create each button adding an onclick function
    for ($i = 0; $i < $numrows; $i++) {
        $row = mysql_fetch_assoc($pagedata);
        $returnNav.= "<div class='navimgoutcont'><div class='navimgincont'><img class='jobnavbarlogo' src='" .
                $row["picture_path"] . "' onClick='incrementJob(" . $row["index"] . ")'  /></div></div>";
    }

    //return the array to the 0 position so that data can be reused in other functions
    $bool = mysql_data_seek($pagedata, 0);
    return $returnNav;
}
?>

<?php

function buildJobOverview($pagedata, $morecontent, $numbertoget) {

    //this function builds the job overview column, the center column

    $returnOverview = "";
    //there should probably be a function here that checks if there actually is data first before it searches to 
    //the proper location
    $numrows = mysql_num_rows($pagedata);

    if ($numrows == 0) {
        $returnOverview = "NO DATA PASSED";
    }
    else {
        //$bool = mysql_data_seek($pagedata, 5);

        $row = mysql_fetch_assoc($pagedata);


        $returnOverview = "<div id='jobsummary' class='jobsummary'>";
        $jobName = "<h1>" . $row["record_name"] . "</h1>";
        $jobTitle = "<sectiontext>" . "Title : " . "</sectiontext>" . "<sectioncontent>" . $row["title"] . "</sectioncontent><br>";
        $jobLocation = "<sectiontext>" . "Location : " . "</sectiontext>" . "<sectioncontent>" . $row["city"] . " " . $row["country"] .
                "</sectioncontent><br>";

        $rawstartdate = strtotime($row["start_date"]);
        $rawenddate = strtotime($row["end_date"]);
        $formattedstartdate = date('M Y', $rawstartdate);
        $formattedenddate = date('M Y', $rawenddate);
        if ($formattedenddate == "Nov -0001") {
            $formattedenddate = "Present";
        }

        $jobDate = "<sectiontext>" . "Date: " . "</sectiontext>" . "<sectioncontent>" . $formattedstartdate . " - " . $formattedenddate .
                "</sectioncontent><br>";

        $jobSummary = "<br><summarytext>" . $row["summary"] . "</summarytext><br>";

        $returnOverview.=$jobName . $jobTitle . $jobLocation . $jobDate . $jobSummary . "<hr><div>" .
                createOverviewMoreContent($morecontent) . "</div></div>";

        $bool = mysql_data_seek($pagedata, 0);
    }
    return $returnOverview;
}
?>

<?php

function buildCarousel($carouseldata) {

    //this function builds the carousel.  Right now I am not including the buttons.  Jeremie, add them.

    $numrows = mysql_num_rows($carouseldata);

    $carousel = "<div id='carousel' class='carousel'>";

    if ($numrows == 0) {
        $carousel.="NO CAROUSEL</div>";
    } else {
        $bool = mysql_data_seek($carouseldata, 0);

        for ($i = 0; $i < $numrows; $i++) {
            $row = mysql_fetch_assoc($carouseldata);
            $carousel.= "<div class='cloudcarouselcontdiv'><div class='cloudcarouselimgdiv'><img class='cloudcarousel' 
                title='" . $row["task_name"] . "' src='" . $row["picture_path"] . "' style='width:100%;' /></div>
                    <p class='cloudcarouseltext'>" . $row["task_name"] . "</p></div>";
        }

        $carousel.=/* "<div id='leftbutton'><input id='left-but'  type='button' onclick='incrementImage()' 
                 * class='leftbutton' value=''/></div><div id='rightbutton'><input id='right-but' type='button' 
                 * onclick='incrementImage()' class='rightbutton' value='' /> */"</div>";

        $bool = mysql_data_seek($carouseldata, 0);
    }

    return $carousel;
}
?>

<?php

function buildTaskSpecificInfo($taskspecificinfo, $taskspecificaddlinfo) {

    //create the bullet points in the box below the carousel

    $numrows = mysql_num_rows($taskspecificinfo);
    $taskinfo = "<div class='textinmorecontent'>";
    if ($numrows == 0) {
        $taskinfo.="NO CONTENT</div>";
    } else {
        $row = mysql_fetch_assoc($taskspecificinfo);

        //each bullet is stored in the database with a data key, this splits the keys apart

        $splitdata = explode('data=\'', $row["task_bullets"]);

        for ($i = 1; $i < count($splitdata); $i++) {
            $procbullet = substr($splitdata[$i], 0, (strrpos($splitdata[$i], '\'')));
            $taskinfo.="<li>" . $procbullet . "</li>";
        }

        $taskinfo.=createExpandContent($taskspecificaddlinfo) . "</div>";
    }
    return $taskinfo;
}
?>

<?php

function createExpandContent($taskspecificaddlinfo) {

    //this function is used to create the content that displays when the user clicks on the more content button in the 
    //box below the carousel.  If there is no content it doesn't return anything.

    $numrows = mysql_num_rows($taskspecificaddlinfo);
    $expandablecontent = "";

    if ($numrows == 0) {
        
    } else {
        $expandablecontent.="<div class='additionaltaskcontent'><div id='expandablecontentbutton' 
            class='expandablecontbut'>More Content</div><div id='expandablecontenthideable' class='expandablecontent'>";

        for ($i = 0; $i < $numrows; $i++) {
            $row = mysql_fetch_assoc($taskspecificaddlinfo);
            $expandablecontent.=$row["description"];
        }
        $expandablecontent.="</div></div>"; //added by jerem 10/04/2012 -> was before: $expandablecontent.="</div>";
    }

    return $expandablecontent;
}
?>

<?php

function createOverviewMoreContent($morecontent) {

    //this is a temporary function.  It only returns the title of the content that is passed to it.  It should be 
    //used to generate the additional content in the job overview section (like videos, pictures, etc).

    $numrows = mysql_num_rows($morecontent);
    if ($numrows == 0) {
        $rawcontent = "JIMMY";
    } else {
        $fetchdata = mysql_fetch_assoc($morecontent);
        $rawcontent = $fetchdata["title"];
    }
    return $rawcontent;
}
?>

<?php

function buildEduPageCards($edudata) {

    $returnCards = "";
    $numrows = mysql_num_rows($edudata);

    //loop through each row and create each button adding an onclick function
    for ($i = 0; $i < $numrows; $i++) {
        $row = mysql_fetch_assoc($edudata);
        $rawstartdate = strtotime($row["start_date"]);
        $rawenddate = strtotime($row["end_date"]);
        $formattedstartdate = date('M Y', $rawstartdate);
        $formattedenddate = date('M Y', $rawenddate);
        if ($formattedenddate == "Nov -0001") {
            $formattedenddate = "Present";
        }

        $returnCards.= "<div id='educard" . ($i + 1) . "' class='educard'><div class='educardleft'><img class='edulogo' 
            src='" . $row["picture_path"] . "' onClick='incrementJob(" . $row["index"] . ")' /></div><div class='educardright'>
                <div class='edutitle'><h1>" . $row["record_name"] . "</h1>" . $formattedstartdate . " - " . $formattedenddate . "
                    </div><div class='edudescription'>" . $row["summary"] . "</div></div></div>";
    }

    //return the array to the 0 position so that data can be reused in other functions
    //$bool = mysql_data_seek($pagedata,0);
    return $returnCards;
}
?>

<?php

function getJobPageData($username) {

    $user = $username;
    //get data for the start page
    $startpagequery = "SELECT * FROM " . "job_education" . " WHERE username = '" . $user . "' AND type_of_record = '" . "Job" . "' 
        ORDER BY " . "start_date DESC";
    $startpageresults = queryMysql($startpagequery);

    //load the data as an associative array to allow for getting columns by name and set the data seek index to 0 to 
    //return it to the beginning of the array
    $startpagedata = mysql_fetch_assoc($startpageresults);
    $bool = mysql_data_seek($startpageresults, 0);
    $startpagefirstjobindex = $startpagedata["index"];

    //get data for the carousel on the start page
    $startpagetaskquery = "SELECT * FROM " . "job_education_task" . " WHERE key_from_job_edu = " . $startpagefirstjobindex;
    $bool = mysql_data_seek($startpageresults, 0);
    $startpagetaskresults = queryMysql($startpagetaskquery);
    $startpagetaskdata = mysql_fetch_assoc($startpagetaskresults);

    //get data for the job overview's additional information section
    $startpagemorecontentquery = "SELECT * FROM " . "job_education_more_info" . " WHERE key_from_job_edu = "
            . $startpagefirstjobindex;
    $startpagemorecontentresults = queryMysql($startpagemorecontentquery);
    //$startpagemorecontentdata = mysql_fetch_assoc($startpagemorecontentdata);
    //get data for the hideable content in the box below the carousel
    $startpagetaskaddlinfoquery = "SELECT * FROM " . "job_education_task_more_info" . " WHERE key_from_job_edu_task = "
            . $startpagetaskdata["index"];
    $startpagetaskaddlinforesults = queryMysql($startpagetaskaddlinfoquery);
    $bool = mysql_data_seek($startpagetaskresults, 0);

    $returnarray = array('pagedata' => $startpageresults, 'taskdata' => $startpagetaskresults, 'taskaddlinfo' =>
        $startpagetaskaddlinforesults, 'morecontent' => $startpagemorecontentresults);

    return $returnarray;
}
?>

