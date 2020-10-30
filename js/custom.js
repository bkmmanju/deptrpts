 $(document).ready(function () {
 	var filter1 = document.getElementById("collapseOne");
 	  	filter1.classList.add("show");
 	var filter2 = document.getElementById("collapseTwo");
 	    filter2.classList.add("show");
 	var filter3 = document.getElementById("collapseThree");
 	    filter3.classList.add("show");
    var filter4 = document.getElementById("collapseFour");
        filter4.classList.add("show");
    var filter5 = document.getElementById("collapseFive");
        filter5.classList.add("show");
 });

 $(document).ready(function() {
        $.ajax({ 
        url: M.cfg.wwwroot+"/local/deptrpts/ajax.php",
        data:{firstload:'yes'},
        type: "POST",
        success: function(data){
            $('#ajaxresult').html(data);
        }
    });
 });


$(document).ready(function() {
	// var startdate ='';
 //    $("#start-date-input").change(function(event) {
 //    var status = "startdate";
 //    filter_data(status);
 //    });

	var enddate ='';
    $("#end-date-input").change(function(event) {
    var status = "enddate";
    filter_data(status);
    });

	var sitelocation ='';
    $("#site_location").change(function(event) {
    var status = "sitelocation";
    filter_data(status);
    });

	var userlocation ='';
    $("#user_location").change(function(event) {
    var status = "userlocation";
    filter_data(status);
    });

	var courselocation ='';
    $("#course_location").change(function(event) {
    var status = "courselocation";
    filter_data(status);
    });

	var sitecategory ='';
    $("#site-category").change(function(event) {
    var status = "sitecategory";
    filter_data(status);
    });

	var usersearch ='';
    $("#usersearch").change(function(event) {
    var status = "usersearch";
    filter_data(status);
    });

	var coursesearch ='';
    $("#coursesearch").change(function(event) {
    var status = "coursesearch";
    filter_data(status);
    });

    //checking which filter is calling.
    var allcourses ='';
    $("#all_course").click(function(event){
    var status = "allcourses";
    filter_data(status);
    });

    var allusers ='';
    $("#all_users").click(function(event){
    var status = "allusers";
    filter_data(status);
    });
});

function filter_data(status){

    var startdate ='';
    var startdate = $("#start-date-input").val();
  
    var enddate ='';
    var enddate = $("#end-date-input").val();
 

    var sitelocation ='';
    var sitelocation = $("#site_location").val();


    var userlocation ='';
    var userlocation = $("#user_location").val();


    var courselocation ='';
    var courselocation = $("#course_location").val();


    var sitecategory ='';
    var sitecategory = $("#site-category").val();
 

    var usersearch ='';
    var usersearch = $("#usersearch").val();


    var coursesearch ='';
    var coursesearch = $("#coursesearch").val();

    $.ajax({ 
        url: M.cfg.wwwroot+"/local/deptrpts/ajax.php",
        data:{status:status,startdate:startdate,enddate:enddate,sitelocation:sitelocation,userlocation:userlocation,
        courselocation:courselocation,sitecategory:sitecategory,usersearch:usersearch, coursesearch:coursesearch},
        type: "POST",
        success: function(data){
            $('#ajaxresult').html(data);
        }
    });
}
