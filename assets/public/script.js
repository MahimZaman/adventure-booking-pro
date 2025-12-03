$ = jQuery;

let abpAjax = abpLocal.ajax;

function abpFormatDate(dateString) {
  const date = new Date(dateString);
  if (isNaN(date.getTime())) {
    throw new Error("Invalid date string. Expected format: YYYY-mm-dd");
  }

  const days = [
    "Sunday",
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday",
  ];
  const months = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
  ];

  const dayName = days[date.getDay()];
  const monthName = months[date.getMonth()];
  const day = date.getDate();
  const year = date.getFullYear();

  return `${dayName}, ${monthName} ${day}, ${year}`;
}

jQuery(document).ready(function ($) {
  // Your JSON value (could also come from AJAX or PHP)
  var jsonRanges = $("[name='pickup_date']").data("dates");

  // Convert JSON to an array of Date objects
  var allowedRanges = Object.values(jsonRanges).map(function (r) {
    return {
      start: new Date(r.start),
      end: new Date(r.end),
    };
  });

  function isInRange(date) {
    return allowedRanges.some(function (range) {
      return date >= range.start && date <= range.end;
    });
  }

  $("[name='pickup_date']").datepicker({
    dateFormat: "yy-mm-dd",
    beforeShowDay: function (date) {
      if (isInRange(date)) {
        return [true, "available-date", "Available"];
      }
      return [false, "unavailable-date", "Unavailable"];
    },
  });

  $(document)
    .on("change", "#pickup_date, #adult, #children", function () {
      if ($("#pickup_date").val() == "") return;
      if ($("#adult").val() == "") return;
      $(".abp-booking-summary").html(`<span class="abp-loader"></span>`).css({
        textAlign: "center",
        display: "flex",
      });
      $.ajax({
        url: abpAjax,
        type: "POST",
        data: {
          action: "load_booking_summary",
          pickUpDate: $("#pickup_date").val(),
          adult: $("#adult").val(),
          children: $("#children").val(),
          post_id: $("#adventure").val(),
        },
        success: function (res) {
          $(".abp-booking-summary").html(res).css({
            textAlign: "left",
            display: "flex",
          });
          $('input[name="adv_option"]').first().trigger("click");
        },
        error: function (err) {
          $(".abp-booking-summary").html(`${err.responseText}`).css({
            display: "flex",
          });
        },
      });
    })
    .on("change", 'input[name="adv_option"]', function () {
      $('input[name="adv_option"]:checked')
        .parent()
        .find('.abp-option-detail [name="adv_time"]')
        .first()
        .trigger("click");
    })
    .on("change", 'input[name="adv_time"]', function () {
      $('input[name="adv_time"]:checked')
        .parent()
        .parent()
        .parent()
        .find('[name="adv_option"]')
        .trigger("click");
    })
    .on("change", "#pickup_date", function () {
      $(".abp-picker-preview .abp-button-text").html(
        abpFormatDate($(this).val())
      );
    })
    .on("click", ".abp-minus", function () {
      $(this).parent().find("input").get(0).stepDown();
      $(this).parent().find("input").trigger("change");
    })
    .on("click", ".abp-plus", function () {
      $(this).parent().find("input").get(0).stepUp();
      $(this).parent().find("input").trigger("change");
    })
    .on("click", ".abp-open-traveller", function (e) {
      e.stopPropagation();
      $(".abp-select-traveller").slideToggle(100);
    })
    .on("click", function (e) {
      // If the menu is visible and click is outside menu & toggle button
      if (
        $(".abp-select-traveller").is(":visible") &&
        !$(e.target).closest(".abp-select-traveller, .abp-open-traveller")
          .length
      ) {
        $(".abp-select-traveller").slideUp(100);
      }
    })
    .on("change", "#adult, #children", function () {
      const adult = $("#adult").val();
      const children = $("#children").val();
      let travellers = `${adult} Adult`;
      if (adult > 1) {
        travellers = `${adult} Adults`;
      }

      if (children == 1) {
        travellers += `, ${children} Child`;
      } else if (children > 1) {
        travellers += `, ${children} Children`;
      }

      $(".abp-open-traveller .abp-button-text").html(travellers);
    })
    .on("change", "#adult", function () {
      const adultVal = $(this).val();
      const adultMax = $(this).attr("max");

      const childMax = parseInt(adultMax) - parseInt(adultVal);
      $("#children").prop("max", childMax);
    })
    .on("change", "#children", function () {
      const allowed = parseInt($("#adult").data("allowed"));
      const childVal = $(this).val();
      const remainAdult = allowed - childVal;

      $("#adult").prop("max", remainAdult);
    });
});
