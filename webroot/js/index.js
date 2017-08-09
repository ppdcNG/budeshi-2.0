function viewProject () {
  var page = document.getElementById('projects-cards');
  var proj = document.getElementById('ubecproject');
  var projOv = document.getElementById('project-overview');
  var ovTab = document.getElementById('ov-table');
   if (page.style.display === 'none')
    {
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
function addParty () {
var party = document.getElementById('party-card');
if (party.style.display === 'none')
{
  party.style.display = 'block';
}
}
function removeParty () {
  var party = document.getElementById('party-card');
  if (party.style.display === 'block')
  {
    party.style.display = 'none';
  }
}
