function pageinit(){//isinitial){
//    //This function takes data that was put into the dynamic div during a navigation change and processes it
//    
//    //document.getElementById("skillcarousel").style.opacity = 0;
//    var rawdynamicdata = document.getElementById("dynamicdataarrays").innerHTML;
//    processdynamicdata(rawdynamicdata,dynamicdata);
//    //Process raw data to strip out delimeters and separate sections
//    
//    secondprocessdynamicdata(dynamicdata[2],dynamic2ddatataskspecific);
//    //process data in [2] element of array to generate task specific HTML for each job/skill
//    
//    var splitchar = "\n";
//    make2darrayfrom1d(dynamicdata,dynamic2ddata,splitchar,0,3);
//    
//    if(isinitial !==false){
//        //If this is the initial pageinit, setup the navigation section of the page, if not, don't
//        setTopNavStyles(3);
        $("#carousel").CloudCarousel({
                                          minScale: 0.4,divSizex: 482,divSizey: 50,xRadius:291,yRadius:35,containerDivsize:100,buttonLeft: $("#left-but"),buttonRight: $("#right-but"),altBox: $("#alt-text"),titleBox: $("#title-text"),bringToFront: true},function(){
                                          //$("#carousel").animate({opacity:1},"slow")
                                          });
//        
//    }
//    document.getElementById("specifictaskinformation").innerHTML = dynamic2ddatataskspecific[0][0];
//    //this reinitializes the content in specific task information because it is loaded before it can register with jquery.  This allows jquery to fade it in and out at startup.
//    //document.getElementByClass("cloudcarousel").style.visibility = block;
}

function incrementJob(jobObject){
    params = "calltype=jobincrement"+"&jobid="+jobObject+"&user="+"jason_koebler"+"&pagetype="+"job"
    request = new ajaxRequest()
    request.open("POST", "/buildpage.php", true)
    request.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
    request.setRequestHeader("Content-length", params.length)
    request.setRequestHeader("Connection", "close")


    request.onreadystatechange = function()
    {
        if(this.readyState == 4)
        {
            if(this.status == 200)
            {
                if(this.responseText != null)
                {
                    
                    var responseText = this.responseText;
                    var ajaxReturn = eval("("+responseText+")");
                    document.getElementById("borderjoboverview").innerHTML = ajaxReturn.centercol;
                    document.getElementById("jobtaskinfo").innerHTML = ajaxReturn.rightcol;
                    $("#carousel").CloudCarousel({
                                                 minScale: 0.4,divSizex: 482,divSizey: 50,xRadius:291,yRadius:35,containerDivsize:100,buttonLeft: $("#left-but"),buttonRight: $("#right-but"),altBox: $("#alt-text"),titleBox: $("#title-text"),bringToFront: true},function(){
                                                 //$("#carousel").animate({opacity:1},"slow")
                                                 });
                }
                else alert("Ajax error: no data received")
            }
            else alert("Ajax error: " + this.statusText)
        }
        
    }
    request.send(params)

}

function ajaxRequest(){
    try
    {
        var request = new XMLHttpRequest();
    }
    catch(e1)
    {
        try
        {
            request = new ActiveXObject("Microsoft.XMLHTTP");
        }
        catch(e3)
        {
            request = false;
        }
    }
    return request;
}

function getPage(pagetype){
    params = "calltype=pagechange"+"&pagetype="+pagetype+"&user="+"jason_koebler";
    request = new ajaxRequest()
    request.open("POST", "buildpage.php", true)
    request.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
    request.setRequestHeader("Content-length", params.length)
    request.setRequestHeader("Connection", "close")
    
    request.onreadystatechange = function()
    {
        if(this.readyState == 4)
        {
            if(this.status == 200)
            {
                if(this.responseText != null)
                {
                    document.getElementById("lowercontainer").innerHTML = this.responseText;

                }
                else alert("Ajax error: no data received")
                    }
            else alert("Ajax error: " + this.statusText)
                }
        
    }
    request.send(params)
}


