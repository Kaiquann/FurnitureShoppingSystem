// ============================================================================
// General Functions
// ============================================================================

// ============================================================================
// Page Load (jQuery)
// ============================================================================

$(() => {
  // Autofocus
  $("form :input:not(button):first").focus();
  $(".err:first").prev().focus();
  $(".err:first").prev().find(":input:first").focus();

  // Confirmation message
  $("[data-confirm]").on("click", (e) => {
    const text = e.target.dataset.confirm || "Are you sure?";
    if (!confirm(text)) {
      e.preventDefault();
      e.stopImmediatePropagation();
    }
  });

  // Initiate GET request
  $("[data-get]").on("click", (e) => {
    e.preventDefault();
    const url = e.target.dataset.get;
    location = url || location;
  });

  // Initiate POST request
  $("[data-post]").on("click", (e) => {
    e.preventDefault();
    const url = e.target.dataset.post;
    const f = $("<form>").appendTo(document.body)[0];
    f.method = "POST";
    f.action = url || location;
    f.submit();
  });

  // Reset form
  $("[type=reset]").on("click", (e) => {
    e.preventDefault();
    location = location;
  });

  // Auto uppercase
  $("[data-upper]").on("input", (e) => {
    const a = e.target.selectionStart;
    const b = e.target.selectionEnd;
    e.target.value = e.target.value.toUpperCase();
    e.target.setSelectionRange(a, b);
  });

  // Photo preview
  // $("label.upload input[type=file]").on("change", (e) => {
  //   const f = e.target.files[0];
  //   const img = $(e.target).siblings("img")[0];

  //   if (!img) return;

  //   img.dataset.src ??= img.src;

  //   if (f?.type.startsWith("image/")) {
  //     img.src = URL.createObjectURL(f);
  //   } else {
  //     img.src = img.dataset.src;
  //     e.target.value = "";
  //   }
  // });

  /**
   * @author: Chong Jun Xiang
   * @description: Combination of drag and drop and file input field for file upload
   *               Supported Multiple Files of Image
   */
  $("label.upload input[type=file]").each((i, e) => {
    const container = $(e).closest("label.upload");
    const img = container.find("img")[0];
    const input = container.find("input[type=file]")[0];
    const isMultiple = e.multiple;
    const dragDropHereStr = "Drag & Drop file here";
    const dropHereStr = "Drop file here";

    container.find("span").text(dragDropHereStr);

    if (img && input) {
      img.dataset.src ??= img.src;
    }

    function preview(e) {
      const files = e.target.files;
      const validFiles = [];
      const invalidFiles = [];
      for (const file of files) {
        if (file.type.startsWith("image/")) {
          validFiles.push(file);
        } else {
          invalidFiles.push(file.name);
        }
      }

      if (invalidFiles.length > 0) {
        return alert(`Invalid files: ${invalidFiles.join(", ")}`);
      }

      if (validFiles.length > 5) {
        return alert("Only allowed maximum of 5 files");
      }

      container.find("img:not(:first-of-type)").remove();

      if (validFiles.length > 0) {
        for (const file of validFiles) {
          const reader = new FileReader();
          reader.onload = (event) => {
            $(img).after(`<img src="${event.target.result}" alt="Uploaded Image">`);
          };
          reader.onerror = () => {
            return alert("Error reading file");
          };
          reader.readAsDataURL(file);
        }
        container.find("span").text(`${validFiles.length} file(s) selected`);
      } else {
        console.error("No file selected");
        img.src = img.dataset.src;
        container.find("span").text(dragDropHereStr);
      }
    }

    input.onchange = (e) => {
      preview(e);
    };

    container.on("dragover", (e) => {
      e.preventDefault();
      container.addClass("dragover");
      container.find("span").text(dropHereStr);
    });

    container.on("dragleave", () => {
      container.removeClass("dragover");
      container.find("span").text(dragDropHereStr);
    });

    container.on("drop", (e) => {
      e.preventDefault();
      container.removeClass("dragover");
      const files = e.originalEvent.dataTransfer.files;
      if (!isMultiple && files.length > 1) {
        return alert("Only allowed maximum of 1 file");
      }
      if (files.length > 0) {
        input.files = files;
        preview({ target: { files } }); // Create a mock event object
      } else {
        console.error("No files dropped");
      }
    });
  });

  // decrease quantity value
  $("[decrease]").on("click", (e) => {
    let value = parseInt($(".quantity").val());
    if (value > 1) {
      $(".quantity").val(value - 1);
    }
  });

  //increase quantity value
  $("[increase]").on("click", (e) => {
    let value = parseInt($(".quantity").val());
    if (value > 0) {
      $(".quantity").val(value + 1);
    }
  });

  /**
   * @author: Chong Jun Xiang
   * @description: General drag and drop functionality for file upload
   */
  $("[allow-drag-drop]").each(function () {
    const container = $(this);
    const input = container.find("input[type=file]");
    const dropzone = container.find(".dropzone");

    dropzone.on("dragover", (e) => {
      e.preventDefault();
      dropzone.addClass("dragover");
    });

    dropzone.on("dragleave", () => {
      dropzone.removeClass("dragover");
    });

    dropzone.on("drop", (e) => {
      e.preventDefault();
      dropzone.removeClass("dragover");
      input.prop("files", e.originalEvent.dataTransfer.files);
    });

    input.on("change", () => {
      container.find("form").submit();
    });
  });

  //show the toggle button content
  $("[toggle-button]").on("click", (e) => {
    $("#dropdown-content").toggleClass("show");
  });

  /**
   * @author: Chong Jun Xiang
   * @description: Toggle the visibility of the file input field
   */
  $('input[name="update_image"]').on("change", function () {
    if ($(this).is(":checked")) {
      $("#update_file_image").show();
      $("#update_image").val("1");
    } else {
      $("#update_file_image").hide();
      $("#update_image").val("0");
    }
  });

  /**
   * @author: Chong Jun Xiang
   * @description: Use to make the delete button on or off when selecting
   */
  $(".select-btn").prop("disabled", true);
  $(".select-box").prop("checked", false);
  $(".select-box").on("change", function () {
    let all_select_box = $(".select-box");
    if ($(all_select_box).filter(":checked").length > 0) {
      $(".select-btn").prop("disabled", false);
    } else {
      $(".select-btn").prop("disabled", true);
    }
  });
  $(".select-submit-btn").on("click", function () {
    let message = $(this).data("message");
    let action = $(this).data("action");
    if (!confirm(message)) {
      return;
    }
    $("#select-form").attr("action", action).submit();
  });
  $(".select-box-all").on("change", function () {
    let isChecked = $(this).is(':checked');
    $('.select-box').prop("checked",  isChecked);
    $(".select-btn").prop("disabled", !isChecked);
  });

  /**
   * @author: Chong Jun Xiang
   * @description: Hotkey for select all the checkbox
   */
  $(document).on("keydown", function (e) {
    // Check if the #select-form element exists on the page
    if ($("#select-form").length > 0) {
      if (e.ctrlKey && e.key === "a" && $(".select-box").length > 0) {
        if ($(".select-btn").prop("disabled")) {
          $(".select-box").prop("checked", true);
          $(".select-btn").prop("disabled", false);
          $(".select-box-all").prop("checked", true);
        } else {
          $(".select-box").prop("checked", false);
          $(".select-btn").prop("disabled", true);
          $(".select-box-all").prop("checked", false);
        }
      }
    }
  });

  /**
   * @author: Chong Jun Xiang
   * @description: When click on the print button, print the content in the printable area
   */
  $("#print_btn").on("click", function () {
    let html_printable = $("[printable]").html();
    let iframe = document.createElement("iframe");
    document.body.appendChild(iframe);
    iframe.style.position = "absolute";
    iframe.style.width = "0";
    iframe.style.height = "0";
    iframe.style.border = "none";
    let doc = iframe.contentWindow.document;
    doc.open();
    doc.write(html_printable);
    doc.write("</body></html>");
    doc.close();
    iframe.contentWindow.onload = function () {
      iframe.contentWindow.print();
      document.body.removeChild(iframe);
    };
  });


  /**
   * @author: Liew Kai Quan
   * @description: product will swap automative
   */
  let $slides = $('.slider-container img');
  let $slideButton = $('.slider-container a');
  let $dots = $('.dots-container .dot');

  let currentSlide = 0;

  function showNextSlide() {
    $slides.eq(currentSlide).removeClass('active').addClass('inactive');
    $slideButton.eq(currentSlide).removeClass('active').addClass('inactive');
    $dots.eq(currentSlide).removeClass('active-dot');

    currentSlide = (currentSlide + 1) % $slides.length;

    $slides.eq(currentSlide).removeClass('inactive').addClass('active');
    $slideButton.eq(currentSlide).removeClass('inactive').addClass('active');
    $dots.eq(currentSlide).addClass('active-dot');
  }

  setInterval(showNextSlide, 2000);

  /**
   * @author: Liew Kai Quan
   * @description: when user click the product slider button will swap the image
   */
  $dots.on('click', function () {
    let slideIndex = $(this).data('slide');
    $slides.eq(currentSlide).removeClass('active').addClass('inactive');
    $slideButton.eq(currentSlide).removeClass('active').addClass('inactive');
    $dots.eq(currentSlide).removeClass('active-dot');
    currentSlide = slideIndex;
    $slides.eq(currentSlide).removeClass('inactive').addClass('active');
    $slideButton.eq(currentSlide).removeClass('inactive').addClass('active');
    $dots.eq(currentSlide).addClass('active-dot');
  });

});
