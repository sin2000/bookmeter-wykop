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
    if(input_text == "") {
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

  function contains_alphanumeric(input_text) {
    var regex;
    regex = /^[a-zA-Z0-9]*$/;
    return regex.test(input_text);
  }

  function validate_optional_tags() {
    var elem = $("#tags_input");
    if((/^[a-zA-Z0-9 #]*$/).test(elem.val()) == false) {
      elem[0].setCustomValidity("bad tags");
    }
    else {
      elem[0].setCustomValidity("");
    }
  }

  function validate_genre() {
    var elem = $("#genre_input");
    var selected_genre = $("#genre_select_input").val();
    if(elem.val() == "" && selected_genre == "inny...") {
      elem[0].setCustomValidity("bad genre");
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

  function get_caret_position(elem) {
    var caret_pos = 0;

    if(document.selection) {
      elem.focus();
      var sel = document.selection.createRange();
      sel.moveStart("character", -elem.value.length);
      caret_pos = sel.text.length;
    }
    else if(elem.selectionStart || elem.selectionStart == '0')
      caret_pos = elem.selectionStart;
  
    return (caret_pos);
  } 

  function set_caret_position(elem, caret_pos) {
    if(elem != null) {
      if(elem.createTextRange) {
        var range = elem.createTextRange();
        range.move('character', caret_pos);
        range.select();
      }
      else {
        if(elem.selectionStart) {
          elem.focus();
          elem.setSelectionRange(caret_pos, caret_pos);
        }
        else
          elem.focus();
      }
    }
  }

  function extract_current_word(input_obj) {
    var pos = get_caret_position(input_obj);
    var curr_term = input_obj.value;
    var substr = curr_term.substring(0, pos);
    var last_idx = substr.lastIndexOf('#');
    if(last_idx >= 0) {
      var word = substr.substr(last_idx + 1);
      if(word.length) {
        return word;
      }
    }

    return "";
  }

  function get_selected_obj(input_obj) {
    var e = input_obj;
    if(e.selectionStart || e.selectionStart == '0') {
      var l = e.selectionEnd - e.selectionStart;
      return { start: e.selectionStart, end: e.selectionEnd, length: l, text: e.value.substr(e.selectionStart, l) };
    }

    if(document.selection) {
      e.focus();
      var r = document.selection.createRange();
      if (r === null) {
        return { start: 0, end: e.value.length, length: 0 }
      }

      var re = e.createTextRange();
      var rc = re.duplicate();
      re.moveToBookmark(r.getBookmark());
      rc.setEndPoint('EndToStart', re);

      return { start: rc.text.length, end: rc.text.length + r.text.length, length: r.text.length, text: r.text };
    }

    return { start: 0, end: e.value.length, length: 0, text: "" };
  }

  function set_selection(input_obj, start, end) {
    var e = input_obj;
    if(e.setSelectionRange) {
        e.focus();
        e.setSelectionRange(start, end);
    } else if(e.createTextRange) {
        var range = e.createTextRange();
        range.collapse(true);
        range.moveEnd('character', end);
        range.moveStart('character', start);
        range.select();
    } else if(e.selectionStart) {
        e.selectionStart = start;
        e.selectionEnd = end;
    }
  }

  function load_autocomplete() {
    $("#tags_input")
      .on("keydown", function(event) {
        if(event.keyCode === $.ui.keyCode.TAB && $(this).autocomplete("instance").menu.active) {
          event.preventDefault();
        }
      })
      .autocomplete({
        delay: 400,
        source: function(request, response) {
          $.getJSON("tag_name_search.php", {
            term: extract_current_word(this.element[0])
          }, response );
        },
        search: function(event, ui) {
          if(event.originalEvent.type != "input")
            return false;

          var term = extract_current_word(this);
          if(term.length < 3 || contains_alphanumeric(term) == false) {
            return false;
          }
        },
        focus: function() {
          return false;
        },
        select: function(event, ui) {
          var pos = get_caret_position(this);
          var substr = this.value.substring(0, pos);
          var last_idx = substr.lastIndexOf('#');
          if(last_idx >= 0) {
            var prepend = this.value.substring(0, last_idx);
            this.value = prepend + '#' + ui.item.value + this.value.substr(pos);
            set_caret_position(this, prepend.length + ui.item.value.length + 1);
          }    
          return false;
        }
      });
  }

  function load_simple_autocomplete(field_id, remote_url, db_field, min_length)
  {
    $("#" + field_id)
      .on("keydown", function(event) {
        if(event.keyCode === $.ui.keyCode.TAB && $(this).autocomplete("instance").menu.active) {
          event.preventDefault();
        }
      })
      .autocomplete({
        delay: 400,
        source: function(request, response) {
          $.getJSON(remote_url, {
            field: db_field,
            term: this.element[0].value.trim()
          }, response);
        },
        search: function(event, ui) {
          if(event.originalEvent.type != "input")
            return false;

          var term = this.value.trim();
          if(term.length < min_length) {
            return false;
          }
        },
        focus: function() {
          return false;
        }
      });
  }

  // Wykop markdown
  function format_mid_text(text, sel, mark, insert_operation) {
    switch(insert_operation) {
      case "both":
        return mark + sel.text.trim().replace(/(\S)(\n+)(\S)/g, "$1" + mark + "$2" + mark + "$3") + mark;
      case "before":
        var newline = text.substr(0, sel.start).match(/\S$/) ? "\n" : "";
        return newline + mark + sel.text.trim().replace(/\n(\S)/g, "\n" + mark + "$1");
      default:
        return "";
    }
  }

  function wykop_markdown_format(textbox_name, mark, placeholder, operation) {
    var input_obj = $("#" + textbox_name);
    input_obj.focus();

    var sel = get_selected_obj(input_obj[0]);
    var text = input_obj.val();
    var select = { start: sel.end, end: sel.end };

    if(sel.text.length == 0) {
      sel.text = placeholder;
      select.end += placeholder.length;
    }

    text = text.substr(0, sel.start) + format_mid_text(text, sel, mark, operation) + text.substr(sel.end);

    select.start += mark.length;
    select.end += mark.length;

    input_obj.val(text);
    set_selection(input_obj[0], select.start, select.end)
  }

  function insert_text_from_src(textbox_name, source_obj) {
    var input_obj = $("#" + textbox_name);
    input_obj.focus();

    var sel = get_selected_obj(input_obj[0]);
    var text = input_obj.val();
    var select = { start: sel.end, end: sel.end };
    var mark = $(source_obj).text().trim();

    text = text.substr(0, sel.start) + mark + text.substr(sel.end);
    select.start = sel.end + mark.length;
    select.end = select.start;

    input_obj.val(text);
    set_selection(input_obj[0], select.start, select.end);
  }

  $("#search_form").submit(function(event) {
    event.preventDefault();

    var form = this;
    if(form.checkValidity() === false) {
      form.classList.add("was-validated");
      return;
    }

    find_book_details($("#search_input").val());
  });

  function book_selected() {
    var idx = $(this).prop("id").split("_")[1];
    var book = window.book_details[idx];

    $("#title_input").val(book.title);
    $("#author_input").val(book.authors);
    $('#genre_select_input option[value="inny..."]').prop("selected", true);
    $("#genre_select_input").change();
    $("#genre_input").val(book.genre);
    $("#isbn_input").val(book.isbn);
    $("#translator_input").val(book.translator);
    $("#publisher_input").val(book.publisher);
    $("#image_url_input").val(get_book_image_url(book.image_url, false));

    $("#search_content").addClass("d-none");
    $("#add_entry_form").removeClass("d-none");

    $("#book_list").empty();
    window.book_details = null;

    scroll_top();
  }

  function get_book_image_url(url_fragment, is_small) {
    var prefix = "https://s.lubimyczytac.pl/upload/books/";
    var suffix = "70x100.jpg";

    if(is_small == false) {
      return (url_fragment == null) ? "" : (prefix + url_fragment);
    }
    else {
      if(url_fragment == null)
        return "img/defbook.png";
    }

    var idx = url_fragment.lastIndexOf("-");
    if(idx >= 0) {
      return prefix + url_fragment.substr(0, idx + 1) + suffix;
    }

    idx = url_fragment.lastIndexOf("/");
    if(idx >= 0) {
      return prefix + url_fragment.substr(0, idx + 1) + suffix;
    }

    return "";
  }

  function show_book_details(details) {
    $("#book_list").empty();

    if(is_undefined(details) || details.length == 0) {
      $("#book_not_found_div").show();
    }
    else {
      window.book_details = details;
      $("#book_not_found_div").hide();

      for(var i = 0; i < details.length; ++i) {
        var clone = $("#book_template").clone();
        clone.prop("id", "book_" + i.toString());
        clone.find(".book_img").prop("src", get_book_image_url(details[i].image_url, true));
        clone.find(".book_title").text(details[i].title);
        clone.find(".book_author").text(details[i].authors);
        clone.find(".book_isbn").text(details[i].isbn);
        clone.find(".book_genre").text(details[i].genre);
        clone.find(".book_publisher").text(details[i].publisher);
        $("#book_list").append(clone);
        clone.removeClass("hide");
      }

      $(".book_item").click(book_selected);
    }
  }

  function find_book_details(book_title) {

    $("#spinner_modal").on("shown.bs.modal", function() {
      $.ajax({
        url: "book_details.php",
        data: {
          "title": book_title
        },

        success: function (response) {
          show_book_details(response);
          hide_spinner();
        },
        error: function () {
          hide_spinner();
          $("#timeout_alert").show();
          scroll_top();
        }
      });
    });

    hide_server_error();
    show_spinner();
  }  

  function autocomplete_book_selected(event, ui) {
    find_book_details(ui.item.value);
  }
  
  $("#success_modal_ok_btn").click(function() {
    location.reload();
  });

  $("#add_entry_form").submit(function(event) {
    event.preventDefault();

    validate_genre();
    validate_isbn();
    validate_optional_tags();

    var form = this;
    if(form.checkValidity() === true) {
      var post_url = $(this).attr("action");
      var request_method = $(this).attr("method");
      var form_data = new FormData(this);

      $("#spinner_modal").on("shown.bs.modal", function() {
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
    validate_genre();
    validate_isbn();
    validate_optional_tags();

    var form = $("#add_entry_form")[0];
    if(form.checkValidity() === true) {
      var post_url = "preview.php";
      var request_method = "POST";
      var form_data = $(form).serialize();

      $("#spinner_modal").on("shown.bs.modal", function() {
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

  $("#genre_select_input").change(function(){
    var val = $(this).val();
    var add_genre_input = $("#genre_input");
    if(val == "inny...") {
      add_genre_input.removeClass("d-none");
      add_genre_input.focus();
    }
    else {
      add_genre_input.addClass("d-none");
      add_genre_input[0].setCustomValidity("");
    }
  });

  $("#descr_bold_btn").click(function(e){
    e.preventDefault();

    wykop_markdown_format("descr_input", "**", "pogrubiony", "both");
  });

  $("#descr_italic_btn").click(function(e){
    e.preventDefault();

    wykop_markdown_format("descr_input", "_", "pochylony", "both");
  });

  $("#descr_quote_btn").click(function(e){
    e.preventDefault();

    wykop_markdown_format("descr_input", "> ", "cytat", "before");
  });

  $("#descr_link_btn").click(function(e){
    e.preventDefault();

    var input_obj = $("#descr_input");
    input_obj.focus();

    var sel = get_selected_obj(input_obj[0]);
    var text = input_obj.val();
    var select = { start: sel.end, end: sel.end };
    var mark = "";

    if(sel.length > 0)
    {
      if(sel.text.indexOf("http") == 0)
        mark = "[opis odnośnika](" + sel.text + ")";
      else
        mark = "[" + sel.text + "](https://www.wykop.pl)";
    }
    else {
      mark = "[opis odnośnika](https://www.wykop.pl)";
      select.start += 1;
      select.end += 15;
    }
    text = text.substr(0, sel.start) + mark + text.substr(sel.end);

    input_obj.val(text);
    set_selection(input_obj[0], select.start, select.end);
  });
  
  $("#descr_code_btn").click(function(e){
    e.preventDefault();

    wykop_markdown_format("descr_input", "`", "kod", "both");
  });

  $("#descr_spoil_btn").click(function(e){
    e.preventDefault();

    wykop_markdown_format("descr_input", "! ", "spolier", "before");
  });

  $("#descr_lenny_btn").click(function(e){
    e.preventDefault();

    insert_text_from_src("descr_input", this);
  });

  $(".lenny").click(function(e){
    e.preventDefault();

    insert_text_from_src("descr_input", this);
  });

  $("#show_main_form").click(function(e){
    e.preventDefault();

    $("#search_content").addClass("d-none");
    $("#add_entry_form").removeClass("d-none");
    scroll_top();
  });

  window.addEventListener("load", function() {
    document.querySelector(".custom-file-input").addEventListener("change", function(e) {
      var fileName = document.getElementById("file_input").files[0].name;
      var nextSibling = e.target.nextElementSibling
      nextSibling.innerText = fileName
    });

    $("#genre_input").on("blur", function() {
      validate_genre();
    });

    $("#isbn_input").on("blur", function() {
      validate_isbn();
    });

    $("#tags_input").on("blur", function() {
      validate_optional_tags();
    });

    $("#file_input_reset_button").click(function(e) {
      e.preventDefault();
      var file_label = $(".custom-file-label");
      var file_label_text = "Dołącz obrazek z dysku";

      if(file_label.text() != file_label_text) {
        $("#file_input").val("");
        $(file_label.text(file_label_text));
      }      
    });

    get_internal_counter();
    load_autocomplete();
    load_simple_autocomplete("search_input", "book_search.php", "title", 2);

    load_simple_autocomplete("genre_input", "autocomplete.php", "genre", 2);
    load_simple_autocomplete("author_input", "autocomplete.php", "authors", 3);
    load_simple_autocomplete("title_input", "autocomplete.php", "title", 3);

    $("#search_input").on("autocompleteselect", autocomplete_book_selected);

    $("#search_input").focus();

  },false);

})();