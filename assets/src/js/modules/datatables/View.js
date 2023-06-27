var $ = require("jquery");
// var dt = require("datatables.net")();
import dt from 'datatables.net';

class View {
  createTable() {
    let table = document.createElement("tbody");
    $(table).datatable();
  }
  execTable( table ) {
    $(table).datatable();
  }
}

export default View;
