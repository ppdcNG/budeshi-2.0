$(document).ready(function(){
    $("#search").keyup(searchInstitution);
})
function delete_mda(id){
    console.log("blah");
    $("#delete-id").val(id);
    var modal = UIkit.modal($("#delete-institution")[0]);
    modal.show();
}

function deleteM(){
    var id = $("#delete-id").val();
    var url = abs_path + "Monitor/delete/" + id;
    console.log(url);
    modalAction("modal", "data", url);
    setTimeout(function() {
        window.location.reload();
    }, 3000);


}
function searchInstitution() {
    var searchText = document.getElementById('search'),
        table = document.getElementById('institutions'),
        rows = table.getElementsByTagName('tr');
       
    searchText = searchText.value.toUpperCase();
    var i,
        institutionName;
    for (i = 1; i < rows.length; i++) {
        institutionName = rows[i].getElementsByTagName('td')[1];
        console.log(typeof(institutionName));
        if (institutionName.innerHTML.toUpperCase().indexOf(searchText) > -1) {
            rows[i].style.display = '';
        }
        else {
            rows[i].style.display = 'none';
        }
    }
}
var require_fields = ["commonName","address", "shortname", "phone", "website"];
$("#add-mda").click(function(){
    let require_fields = ["commonName","address", "shortname", "phone", "website"];
    var valid = validateFields(require_fields);
    if(valid != true){
        UIkit.notification("Missing Required Field " + valid, { status: 'danger', timeout: 3000 });
        return;
    }
    var mda = form2js("mda-details");
    var data = JSON.stringify(mda);
    var url = abs_path + "/Monitor/addMDA"
    modalAction("modal",data,url);
    setTimeout(function() {
        window.location.reload();
    }, 2000);

});

$("#edit-mda").click(function(){
    let require_fields = ["e_commonName", "e_shortname"];
    var valid = validateFields(require_fields);
    if(valid != true){
        UIkit.notification("Missing Required Field " + valid, { status: 'danger', timeout: 3000 });
        return;
    }
    var mda = form2js("mda-edit");
    var data = JSON.stringify(mda);
    var id = $("#e_project_id").val();
    var url = abs_path + "/Monitor/edit/" + id;
    modalAction("modal",data,url);
    setTimeout(function() {
        window.location.reload();
    }, 2000);

});

function edit_callback(data){
    console.log(data);
    var return_data = proccessJson(data);
    console.log(return_data);
    if(return_data.ajaxstatus == "success"){
        $("#e_commonName").val(return_data.e_name);
        $("#e_address").val(return_data.e_address);
        $("#e_phone").val(return_data.e_phone);
        $("#e_shortname").val(return_data.e_short_name);
        $("#e_sector").val(return_data.e_sector);
        $("#e_website").val(return_data.e_sector);
        $("#e_email").val(return_data.e_email);
        var modal = UIkit.modal("#edit-institution");
        modal.show();
    }
    else {
        UIkit.notification("failed operation", { status: 'danger', timeout: 3000 });
    }
}

function editmodal(id) {
    console.log("started edit modal");
    var url = abs_path + "Monitor/ajaxget/";
    $("#e_project_id").val(id);
    get_modal_params(url, id, edit_callback);
    

}
function get_modal_params(to_url, data_id, success_callback) {
    var dataObj = {
        id: data_id
    }
    var return_data = false;
    $.ajax({
        type: "post",
        data: dataObj,
        url: to_url,
        success: success_callback
    });

}
