

$(document).ready(function () {
    init();
    $('#select-org').select2({
        ajax: {
            url: ABS_PATH + "release/getorg",
            type: "get",
            dataType: 'json',
            data: function (params) {
                console.log(params.term);
                return { searchText: params.term }
            },
            processResults: function (data) {
                console.log(data);
                //var obj = JSON.parse(data);
                //console.log(obj);
                return {
                    results: data
                }
            },
            cache: true
        }
    });

    ////events
    $("#finderFind").click(function () {
        let data = {};
        data.state = $("#select-state").val();
        data.mda = $("#select-mda").val();
        data.year = $("#select-year").val();
        data.contractor = $("#select-org").val();
        data.stage = $("#select-stage").val();
        data.text = $("#search-text").val();
        data.status = $("#select-status").val();
        data.monitored = $("#select-monitored").val();
        console.log(data);
        let json = JSON.stringify(data);
        console.log(json);
        let url = ABS_PATH + "Home/search";
        ajaxrequest("modal", json, url, handleSearch);

    });
    $(".paglinks").click(function () {
        console.log("blah");
        var text = $(this).text();
        loadPage(text);
    });
    $("#chart-type").change(hanldeChartChange);
    $("#by").change(handleChartChange);

});




function init() {
    let url = ABS_PATH + "Home/getProjectArray/";
    ajaxrequest("mymodal", "data-prog", url, handleInit);
}
////handlers
function handleChartChange(){
    console.log("blah change")
    let type = $("#chart-type").val();
    let by = $("#by").val();
    numProjectChart(by,type)
    contractBudgetChart(by,type);
}
function handleInit(data) {
    console.log(data);
    data_array = proccessJson(data);
    numProjectChart("short_name","column");
    contractBudgetChart("short_name","column");
    

}
function handleSearch(data) {
    console.log(data);
    var result = proccessJson(data);
    if (result.length <= 0) {
        UIkit.notification("No results found please refine your search", { status: "danger", timeout: 2000 });
        return;
    }
    data_array = result.array
    $("#min").text(result.min);
    $("#max").text(result.max);
    $("#avg").text(result.avg)
    $("#num_project").text(data_array.length);
    loadPage(1);
    table.destroy();
    table = $("#bud-table").DataTable({
        data: data_array,
        columns: [
            {
                data: "title",
                render: function (data, type, row) {
                    return '<a href = "' + ABS_PATH + "/Home/project/" + row.id + '">' + row.title + '</a>';
                }
            },
            { data: "state" },
            { data: "name" },
            { data: "amount" },
            { data: "budget_amount" },
            { data: "year" },
            { data: "short_name" },
            { data: "status"},
            { data: "monitored",
              render:function(data,type,row){
                  if(data == "yes"){
                      return '<span class="uk-margin-small-right" uk-icon="icon: check"></span>';
                  }
                  else{
                      return '<span class="uk-margin-small-right" uk-icon="icon: close"></span>';
                  }
              }}
        ],
        dom: 'Bfrtip',
        buttons: ["copy", "csv", "pdf"]
    });
    numProjectChart("short_name","column");
    contractBudgetChart("short_name","column");

    
}

function cardPage($number) {
    var links = $(".paglinks");

}
function loadPage(page) {
    if (page == "") {
        return;
    }
    currpage = page;
    var start = (page - 1) * PER_PAGE;
    var end = start + PER_PAGE;
    console.log(end)
    console.log(data_array.length);
    if (end > data_array.length) {
        end = data_array.length;
    }
    var cards = "";
    for (let i = start; i < end; i++) {
        var info = data_array[i];
        cards += renderProjectCard(info.id, info.short_name, info.title, info.description, info.state, info.year);
    }
    $("#cards").html(cards);
    console.log(page);
    renderPageLinks(page);

}
function next() {
    console.log("next");
    currpage = parseInt(currpage) + 1;
    nunPages = Math.ceil(data_array.length/PER_PAGE);
    if(currpage >= nunPages){
        currpage = nunPages;
    }
    console.log(currpage);
    loadPage(currpage);
}
function prev() {
    currpage -= 1;
    if(currpage < 1){
        currpage = 1;
    }

    loadPage(currpage);
}

function numProjectChart(by, type){
    var data = getUniqueCount(by);
    console.log(data);
    var d = {};
    var xaxis = [];
    var values = [];
    for(var key in data){
        xaxis.push(key);
        var d = {};
        d.name = key
        d.y = data[key]
        values.push(d);
    }
    console.log(xaxis);
    console.log(values);
    activityChart("charts-container",xaxis,values,type,by);

}
function contractBudgetChart(by, type){
    var contract_series = {name: "contract Amount",
                        data: null};
    var budget_series = {name : "Budget Amount",
                        data: null};
    var categories = getUniqueCount(by);
    var xaxis = [];
    var ctrtData = [];
    var bdgData = [];
    for(let key in  categories){
        ctrtData.push(Amount(by,key,"amount"));
        bdgData.push(Amount(by,key,"budget_amount"))
        xaxis.push(key)
    }
    contract_series.data = ctrtData;
    budget_series.data = bdgData;
    console.log(contract_series);
    console.log(budget_series);
    var data = [contract_series,budget_series];
    console.log(data);
    AmountChart("amount-chart",xaxis,data,type,by);
    
}
function viewProject(id){
    var url = ABS_PATH + "Home/project/" + id;
    window.location = url;
}
function loadMap(){
    console.log("blah");
    var locat = {lat: 9.072264, lng: 7.491302};
    if(maploaded && (map == undefined)){
    console.log("yeah");
    map = new google.maps.Map(document.getElementById('map'), {
          center: {lat: 9.072264, lng: 7.491302},
          zoom: 6.5
        });
        map.addListener("dragend",function(){
          map.setCenter(locat);
          console.log(map.center);
          console.log(map.getZoom());
          drawMap();
        });
    }
}
