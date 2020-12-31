$(document).ready(function () {
  $("#bookmeter_grid tfoot th").each(function() {
    $(this).html('<input type="text" placeholder="Szukaj" />');
  });

  // DataTable
  $("#bookmeter_grid").DataTable({
    deferRender: true,
    processing: true,
    language: {
      url: "../data/i18n_datatables_polish.json"
    },
    ajax: {
      url: "bookmeter_data.php",
      dataSrc: ""
    },
    createdRow: function(row, data, dataIndex, cells) {
      $(cells[0]).html('<a href="https://www.wykop.pl/wpis/' + data[0] + '" target="_blank" title="Otwórz wpis">' + data[0] + '</a>');
      var color = "deeppink";
      if(data[3] == "niebieski")
        color = "blue";

      $(cells[3]).html('<span style="color: ' + color + ';">' + data[3] + '</span>');
    },
    columnDefs: [
      { "width": "30%", "targets": [ 4, 5 ] }
      // { "type": "num", "targets": 7 }
    ],

    initComplete: function () {
      this.api().columns().every(function() {
        var that = this;

        $("input", this.footer()).on("keyup change clear", function() {
          if (that.search() !== this.value){
            that
              .search(this.value)
              .draw();
          }
        });
      });
    }
  });
});