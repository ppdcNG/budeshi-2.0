function delete_org(id){
    console.log("blah");
    $("#delete_id").val(id);
    var modal = UIkit.modal($("#delete-org")[0]);
    modal.show();
}

function deleteO(){
    var id = $("#delete_id").val();
    var url = abs_path + "Organisation/delete/" + id;
    console.log(url);
    modalAction("modal", "data", url);
    setTimeout(function() {
        window.location.reload();
    }, 3000);


}
var require_fields = ["commonName","address", "shortname", "phone", "website"];
$("#add-org").click(function(){
    let require_fields = ["aname","aid", "aphone", "aemail", "auri", "aregion", "astreetName"];
    var valid = validateFields(require_fields);
    if(valid != true){
        UIkit.notification("Missing Required Field " + valid, { status: 'danger', timeout: 3000 });
        return;
    }
    var mda = form2js("add-organisation");
    console.log(mda)
    var data = JSON.stringify(mda);
    var url = abs_path + "/Organisation/addOrg"
    modalAction("modal",data,url);
    setTimeout(function() {
        window.location.reload();
    }, 2000);

});

$("#edit-org").click(function(){
    let require_fields = ["name","id", "phone", "email", "uri", "region", "streetName"];
    var valid = validateFields(require_fields);
    if(valid != true){
        UIkit.notification("Missing Required Field " + valid, { status: 'danger', timeout: 3000 });
        return;
    }
    var mda = form2js("organisation");
    console.log(mda)
    var data = JSON.stringify(mda);
    var id = $("#org_id").val();
    var url = abs_path + "/Organisation/edit/" + id;
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
        $("#name").val(return_data.name);
        $("#legalName").val(return_data.name);
        $("#id").val(return_data.rc_no);
        $("#streetName").val(return_data.address);
        $("#locality").val(return_data.lga);
        $("#region").val(return_data.state);
        $("#uri").val(return_data.url);
        $("#postalCode").val(return_data.postal_code);
        $("#phone").val(return_data.phone);
        $("#email").val(return_data.email);
        $("#contactName").val(return_data.contact_name);
        var modal = UIkit.modal("#edit-institution");
        modal.show();
    }
    else {
        UIkit.notification("failed operation", { status: 'danger', timeout: 3000 });
    }
}

function editmodal(id) {
    console.log("started edit modal");
    var url = abs_path + "Organisation/ajaxget/";
    $("#org_id").val(id);
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
