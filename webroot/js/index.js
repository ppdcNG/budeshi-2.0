function viewProject() {
  var page = document.getElementById('projects-cards');
  var proj = document.getElementById('ubecproject');
  var projOv = document.getElementById('project-overview');
  var ovTab = document.getElementById('ov-table');
  if (page.style.display === 'none') {
    page.style.display = 'block';
    proj.style.display = 'none';
    projOv.style.display = 'none';
    ovTab.style.display = 'block';

  } else {
    page.style.display = 'none';
    proj.style.display = 'block';
    projOv.style.display = 'block';
    ovTab.style.display = 'none';
  }
}
function addParty() {
  var party = document.getElementById('party-card');
  if (party.style.display === 'none') {
    party.style.display = 'block';
  }
}
function removeParty() {
  var party = document.getElementById('party-card');
  if (party.style.display === 'block') {
    party.style.display = 'none';
  }
}
function showProcEntDet() {
  var id = document.getElementById('procEntID');
  var name = document.getElementById('procEntName');
  if (id.style.display === 'none' && name.style.display === 'none') {
    id.style.display = 'block';
    name.style.display = 'block';
  }
}
function addItem() {
  var item = document.getElementById('item-card');
  if (item.style.display === 'none') {
    item.style.display = 'block';
  }
}
function removeItem() {
  var item = document.getElementById('item-card');
  if (item.style.display === 'block') {
    item.style.display = 'none';
  }
}

function addDocument() {
  var document1 = document.getElementById('document-card');
  if (document1.style.display === 'none') {
    document1.style.display = 'block';
  }
}
function removeDocument() {
  var document1 = document.getElementById('document-card');
  if (document1.style.display === 'block') {
    document1.style.display = 'none';
  }
}
function addAmendment() {
  var amendment = document.getElementById('amendment-card');
  if (amendment.style.display === 'none') {
    amendment.style.display = 'block';
  }
}
function removeAmentment() {
  var amendment = document.getElementById('amendment-card');
  if (amendment.style.display === 'block') {
    amendment.style.display = 'none';
  }
}
function addTransaction() {
  var transaction = document.getElementById('transaction-card');
  if (transaction.style.display === 'none') {
    transaction.style.display = 'block';
  }
}
function removeTransaction() {
  var transaction = document.getElementById('transaction-card');
  if (transaction.style.display === 'block') {
    transaction.style.display = 'none';
  }
}
function scroll() {
  var elmnt = document.getElementById("who");
elmnt.scrollIntoView();
}
