(function() {

  "use strict";

  function show_spinner() {
    $("#spinner_modal").modal({backdrop: "static", keyboard: false});
  }

  function hide_spinner() {
    $("#spinner_modal").modal({backdrop: false});
    $("#spinner_modal").modal("hide");
    $("#spinner_modal").off("shown.bs.modal");
  }

  function show_success_modal() {
    $("#success_modal").modal({backdrop: "static", keyboard: false});
  }

  function scroll_top() {
    $('html, body').animate({scrollTop: '0px'}, 300);
  }

  function show_server_error(error_text) {
    $("#srv_err_text").text(error_text);
    $("#srv_err_alert").show();
  }

  function hide_server_error() {
    $("#srv_err_alert").hide();
  }

  function is_undefined(x) {
    return (typeof x === "undefined");
  }

  function reset_form() {
    $("#add_entry_form").trigger("reset");
    reset_star_rating();
  }

  function get_internal_counter() {

    $.ajax({
      url: "./counter.php",
      cache: false,

      success: function(response) {
        $("#counter_spinner").hide();
        $("#book_counter_input").text(response);
      },
      error: function() {
        console.log("error");
      },
      complete: function() {
        $("#counter_spinner").hide();
      }
    });
  }

  $("#success_modal_ok_btn").click(function() {
    location.reload();
  });

  $("#add_entry_form").submit(function(event) {
    event.preventDefault();
    //event.stopPropagation();
    var form = this;
    if(form.checkValidity() === true) {
      var post_url = $(this).attr("action");
      var request_method = $(this).attr("method");
      var form_data = new FormData(this);

      $("#spinner_modal").on("shown.bs.modal", function () {
        $.ajax({
          url: post_url,
          type: request_method,
          data: form_data,
          contentType: false,
          cache: false,
          processData: false,

          success: function(response) {
            hide_spinner();
            if(is_undefined(response) == false && is_undefined(response.errmsg) == false) {
              if(response.errmsg == "") {
                show_success_modal();
              }
              else {
                show_server_error(response.errmsg);
                scroll_top();
              }
            }
            
            //reset_form();
            console.log(response);
            //location.reload();
          },
          error: function() {
            hide_spinner();
            $("#timeout_alert").show();
            scroll_top();
            console.log("error");
          }
        });
      });

      hide_server_error();
      show_spinner();
    }
    form.classList.add("was-validated");
  });
  
  window.addEventListener("load", function() {    

    document.querySelector(".custom-file-input").addEventListener("change", function(e) {
      var fileName = document.getElementById("file_input").files[0].name;
      var nextSibling = e.target.nextElementSibling
      nextSibling.innerText = fileName
    })

    get_internal_counter();
  
  },false);

})();