$(document).ready(function(){
    var pro_id = $("#project_id").val();
    var type = $("#rel_type").val();
    var dataObject = { data: "get" }
    let to_url = abs_path + "release/getarrays/"+pro_id + "/" + type;
    console.log(to_url);
    var element = $("#wait-modal")[0];
    //var modal = UIkit.modal(element);
    //modal.show();
    $.ajax({
        type: "post",
        data: dataObject,
        url: to_url,
        content: "application/json",
        success: function (data) {
            console.log(data);
            var returnedData = proccessJson(data);
            if (returnedData.ajaxstatus == "success") {
               if (returnedData.parties && Array.isArray(returnedData.parties)){
                   parties = returnedData.parties
                   console.log(parties)
               }
                if(returnedData.milestones && Array.isArray(returnedData.milestones)){
                    milestones = returnedData.milestones;
                    console.log(milestones);
                }
                console.log(returnedData.items);
                if(returnedData.items && Array.isArray(returnedData.items)){
                    items = returnedData.items;
                    console.log(items);
                }
                if(returnedData.documents && Array.isArray(returnedData.documents)){
                    documents = returnedData.documents
                    console.log(documents);
                }
                //modal.hide();
                UIkit.notification(returnedData.message, { status: 'danger', timeout: 3000 });
            }
            else {
                UIkit.notification(returnedData.message, { status: 'danger', timeout: 3000 });
                console.log(returnedData.message);
            }
        }
    });
});
