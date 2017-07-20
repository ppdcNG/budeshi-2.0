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
