$(document).ready(function () {

  function create_cookie(name, value, days) {
    var expires;

    if(days) {
      var date = new Date();
      date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
      expires = "; expires=" + date.toGMTString();
    }
    else {
      expires = "";
    }
    document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/; secure";
  }

  function validate_logins() {
    var elem = $("#ignored_logins");
    if((/^[a-zA-Z0-9 _-]*$/).test(elem.val()) == false) {
      elem[0].setCustomValidity("bad logins");
    }
    else {
      elem[0].setCustomValidity("");
    }
  }

  $("#bookmeter_srv_grid tfoot th").each(function() {
    $(this).html('<input class="column_search" type="text" width="10%" placeholder="Filtr" />');
  });

  // DataTable
  var table = $("#bookmeter_srv_grid").DataTable({
    deferRender: true,
    processing: true,
    searchDelay: 500,
    fixedHeader: {
      footer: true
    },
    serverSide: true,
    language: {
      url: "../data/i18n_datatables_polish.json"
    },
    ajax: "bookmeter_srv_data.php",
    order: [
      [ 1, "desc" ]
    ],
    createdRow: function(row, data, dataIndex, cells) {
      $(cells[0]).html('<a href="https://www.wykop.pl/wpis/' + data[0] + '" target="_blank" title="Otwórz wpis">' + data[0] + '</a>');
      
      var color = "deeppink";
      if(data[3] == "niebieski")
        color = "blue";
      $(cells[3]).html('<span style="color: ' + color + ';">' + data[3] + '</span>');
    },
    columns: [
      { "width": "4%" },
      { "width": "10%" },
      { "width": "13%" },
      { "width": "4%" },
      { "width": "20%" },
      { "width": "26%" },
      { "width": "14%" },
      { "width": "5%", className: "text-center text-danger"},
      { "width": "4%", className: "text-center text-success" },
    ],

    initComplete: function () {
      this.api().columns().every(function() {
        var that = this;

        $("input", this.footer()).on("keyup change clear", function() {
          var val = this.value.toLowerCase();
          clearTimeout(window.searchtid);
          if(that.search() !== val) {
            window.searchtid = setTimeout(() => {
              that.search(val).draw();
            }, 500);
            
          }
        });
      });
    }
  });

  $("#adv_filter_form").submit(function(event) {
    event.preventDefault();

    $("#apply_adv_filters_button").prop("disabled", true);

    validate_logins();

    var form = this;
    if(form.checkValidity() === true) {

      var ignored_logins_value = $("#ignored_logins").val().trim();
      create_cookie("setting_ignored_logins", ignored_logins_value, 600);
      table.ajax.reload(function() { 
        setTimeout(function() {
          $("#apply_adv_filters_button").prop("disabled", false);
        }, 700);
      });
    }
    form.classList.add("was-validated");
  });
});