var milestones = [];
var parties = [];
var documents = [];

console.log(abs_path + "release/getorg");
var require_fields = ["release_id", "project", "date"];
$("#save-release").click(function () {
    var valid = validateFields(require_fields);
    if(valid != true){
        UIkit.notification("Missing Required Field " + valid, { status: 'danger', timeout: 3000 });
        return;
    }
    console.log("blah");
    var release = form2js("release-form",".",false);
    var adate = new Date();
    release.date = adate.toISOString();
    release.parties = parties.filter(filter_undefined);
    release.planning.milestones = milestones.filter(filter_undefined);
    release.milestone = undefined;
    var value = JSON.stringify(release);
    console.log(value);
    var id = $("#project_id").val();
    var mda = $("#mda-id").val();
    var url = abs_path + "release/transactadd/planning/" + id + "/" + mda;
    modalAction("#planning",value,url);
    //setTimeout(function() {window.history.back();}, 3000);
});

$("#edit-release").click(function () {
    var valid = validateFields(require_fields);
    if(valid != true){
        UIkit.notification("Missing Required Field " + valid, { status: 'danger', timeout: 3000 });
        return;
    }
    console.log("blah");
    var release = form2js("release-form");
    var adate = new Date();
    release.date = adate.toISOString();
    release.parties = parties;
    release.planning.milestones = milestones;
    release.milestone = undefined;
    var value = JSON.stringify(release);
    console.log(value);
    var id = $("#project_id").val();
    var mda = $("#mda-id").val();
    var url = abs_path + "release/transactedit/planning/" + id + "/" + mda;
    modalAction("#planning",value,url);
    setTimeout(function() {
        window.history.back();
    }, 3000);
});

