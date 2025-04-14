document.getElementById('show-filt').addEventListener('click', ()=> {
  document.getElementById('filt').classList.toggle('filt-cardd-opn');
  document.getElementById('filt').classList.toggle('filt-cardd-close');
  document.getElementById('arr').classList.toggle('rot');
  document.getElementById('show-filt-text').innerHTML =  document.getElementById('show-filt-text').innerHTML === "Filters &nbsp;" ? "Filters &nbsp;" : "Filters &nbsp;";
});