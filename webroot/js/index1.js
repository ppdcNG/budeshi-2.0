const ABS_PATH = 'http://localhost/budeshi-2.0/webroot/';
const PER_PAGE = 8;
var loaded = false;
var currpage = 1;
var data_array = [];
var waitmodal;
var columnMap = {
    short_name : "MDA",
    year : "Year",
    budget_amount : "Budget Amount",
    amount : "Contract Amount",
    state: "Location"
}
var map;
var markers;
var table;
function ajaxrequest(modal, json_data, to_url, call_back) {
    var dataObject = { data: json_data }
    $.ajax({
        type: "post",
        data: dataObject,
        url: to_url,
        content: "application/json",
        success: call_back
    });
}
function initComponents(){

}

function proccessJson(data) {
    var obj = JSON.parse(data);
    return obj;
}

function renderProjectCard(id, mda, title, des, state, year) {
    var yr = (year =="" || year == "null" || year == null) ? "N/A": year;
    var html = `
                <div class = "project-card">
                    
					<div class="uk-card uk-card-default uk-card-hover uk-card-body" onclick="viewProject(\'` + id + `\')">
					<div class="uk-card-badge uk-label">` + yr + `</div>
                    <h3 class="uk-card-title uk-heading-bullet">` + state + `</h3>
                    <p>`+mda+`</p>
					<p class="uk-text-truncate" title="`+title+`">` + title + `</p>
					</div>
				</div>
                `;
    return html;
}

function renderPageLinks(page) {
    $("#prvbtn").prop("disabled", false);
    $("#nxtbtn").prop("disabled", false);
    page = parseInt(page)

    var startPage = page - 4;
    var endPage = page + 4;
    var pages = Math.ceil(data_array.length / PER_PAGE);
    if (pages <= 1) {
        pages = 1;
    }
    console.log(pages);

    if (startPage <= 0) {
        endPage -= (startPage - 1);
        startPage = 1;
    }

    if (endPage > pages) {
        startPage -= (endPage - pages)
        endPage = pages;
    }
    if ((endPage >= pages) && (startPage <= 0)) {
        startPage = 1;
        endPage = pages;

    }


    var page_nums = [];
    console.log(startPage);
    console.log(endPage);
    for (let i = startPage; i <= endPage; i++) {
        page_nums.push(i);
    }
    var links = $(".paglinks").each(function (index) {
        $(this).removeClass("pageactive");
        if (page_nums["index"] == undefined) { }
        if (page == page_nums[index]) $(this).addClass("pageactive");
        if (page_nums[index] == undefined) {
            $(this).text("")
        }
        $(this).text(page_nums[index]);
    });
    console.log(page_nums);



    if (page == 1) {
        $("#prvbtn").prop("disabled",true);
    }
    else {
        //$("#prev").className = "";
    }
    if (page >= pages) {
        $("#nxtbtn").prop("disabled", true);
    }
    else {
        //$("#next").className = "";
    }
}

///Charts 
function activityChart(id, category, column_series, chart_type, by) {

    let chart_title = chart_type + " chart number of procurement/projects per " + columnMap[by];
    let yLabel = "number of procurement"
    Highcharts.chart(id, {
        chart: {
            type: chart_type
        },
        title: {
            text: chart_title
        },
        xAxis: {
            categories: category
        },
        yAxis: {
            min: 0,
            title: {
                text: 'number of procurement activity'
            }
        },
        credits:{
            enabled: true,
            href: "budeshi.ng",
            position: {

            },
            style:{
                "cursor":"pointer",
                "color": "#999999",
                "fontSize":"10px"
            },
            text: "budeshi.ng"
        },
        legend: {
            reversed: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: [{
            name: "number of Procurement Activity" ,
            data: column_series, }]
    });
}

function AmountChart(id, category, Data, chart_type, by) {

    let chart_title = chart_type + " chart showing the budget and contract amount by " + columnMap[by];
    let yLabel = "Amounts in Naira"
    Highcharts.chart(id, {
        chart: {
            type: chart_type
        },
        credits:{
            enabled: true,
            href: "budeshi.ng",
            position: {

            },
            style:{
                "cursor":"pointer",
                "color": "#999999",
                "fontSize":"10px"
            },
            text: "budeshi.ng"
        },
        title: {
            text: chart_title
        },
        xAxis: {
            categories: category
        },
        yAxis: {
            min: 0,
            title: {
                text: 'contract and budget amount'
            }
        },
        legend: {
            reversed: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: Data
    });
}


///Aray funcions

function getUniqueCount(column) {
    console.log("blah start");
    let count = {};
    for (let i = 0; i < data_array.length; i++) {
        let cell = data_array[i][column];
        console.log(cell);
        if (cell == 0 || cell == "" || cell == undefined) {
            continue;
        }
        else {
            count[cell] = 1 + (count[cell] || 0);
        }
    }
    return count;
}
function Amount(by, where, type = "budget_amount") {
    var amount = 0;
    for (let i = 0; i < data_array.length; i++) {
        let row = data_array[i][by];
        if (row == where) {
            if (data_array[i][type] == undefined || data_array[i][type] == "") {
                amount += 0
            }
            else {
                amount += parseInt(data_array[i][type]);
            };
        }
    }
    return amount;
}
function getStateCordinates(){
    console.log("blah start");
    let count = {};
    let number = {};
    for (let i = 0; i < data_array.length; i++) {
        let cell = data_array[i]["state"];
        console.log(cell);
        if (cell == 0 || cell == "" || cell == undefined) {
            continue;
        }
        else {
            number[cell] = 1 + (number[cell] || 0);
            count[cell] = {

                lat:data_array[i]["latitude"],
                lng: data_array[i]["longitude"],
                number: number[cell]
        
                
            }

        }
    }
    
    return count;
}
function drawMap(){
    markers = [];
    cordinates = getStateCordinates();
    console.log(cordinates);
    for(cord in cordinates){
        if(true){
        var mark = new google.maps.Marker({
            position: {lat:parseInt(cordinates[cord].lat), lng :parseInt(cordinates[cord].lng)},
            title: cordinates[cord].number.toString(),
        });
        console.log(mark);
        mark.setMap(map);
        markers.push(mark);
        }
    }

}



