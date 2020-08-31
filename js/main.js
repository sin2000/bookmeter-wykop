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

  function show_preview_modal(body) {
    $("#preview_content").html(body);
    $("#preview_modal").modal({backdrop: "static", keyboard: false});
  }

  function scroll_top() {
    $("html, body").animate({scrollTop: "0px"}, 300);
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

  function validate_isbn() {

    var elem = $("#isbn_input");

    var input_text = elem.val();
    if (input_text == "")
    {
      elem[0].setCustomValidity("");
      return;
    }

    input_text = input_text.trim();
    var is_valid = false;
    var regex = /^(?:ISBN(?:-1[03])?:? )?(?=[0-9X]{10}$|(?=(?:[0-9]+[- ]){3})[- 0-9X]{13}$|97[89][0-9]{10}$|(?=(?:[0-9]+[- ]){4})[- 0-9]{17}$)(?:97[89][- ]?)?[0-9]{1,5}[- ]?[0-9]+[- ]?[0-9]+[- ]?[0-9X]$/;
    if(regex.test(input_text)) {
      var chars = input_text.replace(/[- ]|^ISBN(?:-1[03])?:?/g, "").split("");
      var last = chars.pop();
      var sum = 0;
      var check, i;

      if(chars.length == 9) {
        chars.reverse();
        for(i = 0; i < chars.length; i++) {
          sum += (i + 2) * parseInt(chars[i], 10);
        }
        check = 11 - (sum % 11);
        if(check == 10) {
          check = "X";
        } else if(check == 11) {
          check = "0";
        }
      } else {
        for(i = 0; i < chars.length; i++) {
          sum += (i % 2 * 2 + 1) * parseInt(chars[i], 10);
        }
        check = 10 - (sum % 10);
        if(check == 10) {
          check = "0";
        }
      }

      if(check == last) {
        is_valid = true;
      }
    }

    if(is_valid == false) {
      elem[0].setCustomValidity("bad ISBN");
    }
    else {
      elem[0].setCustomValidity("");
    }
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

    validate_isbn();

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

  $("#preview_button").click(function() {
    validate_isbn();

    var form = $("#add_entry_form")[0];
    if(form.checkValidity() === true) {
      var post_url = "preview.php";
      var request_method = "POST";
      var form_data = $(form).serialize();

      $("#spinner_modal").on("shown.bs.modal", function () {
        $.ajax({
          url: post_url,
          type: request_method,
          data: form_data,

          success: function(response) {
            hide_spinner();
            if(is_undefined(response) == false && is_undefined(response.errmsg) == false) {
              if(response.errmsg == "" && is_undefined(response.body) == false) {
                show_preview_modal(response.body);
              }
              else {
                show_server_error(response.errmsg);
                scroll_top();
              }
            }
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
    });

    $("#isbn_input").on("blur", function () {
      validate_isbn();
    });

    $("#file_input_reset_button").click(function (e) {
      e.preventDefault();
      var file_label = $(".custom-file-label");
      var file_label_text = "Dołącz obrazek z dysku";

      if(file_label.text() != file_label_text) {
        $("#file_input").val("");
        $(file_label.text(file_label_text));
      }      
    });

    get_internal_counter();
  
  },false);

})();