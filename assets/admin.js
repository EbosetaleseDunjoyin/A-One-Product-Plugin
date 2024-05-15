const root_url = aOne_admin_params.root_url;
const admin_url = aOne_admin_params.ajax_url;

jQuery(document).ready(function ($) {
  $("input[name='carbon_fields_compact_input[_trigger_sync_product]'").click(
    function () {
      $(this).attr("disabled", true);
      $(this).val("Refreshing.......");
      let $this = $(this);

      let apiUrl = `${root_url}v1/aOneProduct/testProductsAndSave`;
      $.ajax({
        type: "get",
        url: apiUrl,
        // data: body,
        dataType: "json", // Explicitly specify JSON as the expected response

        success: function (response) {
          if (response.status) {
            $this.attr("disabled", false);
            $this.val("Successfully retrieved.");
          } else {
            $this.attr("disabled", false);
            $this.val(
              "Issue occurred retrieving products: " + response.message
            );
          }
        },
        error: function (error) {
          console.error("Request failed. Status: " + error.status);
          $this.val("Request failed. Status: " + error.status);
          console.log(error);
          $this.attr("disabled", false);
          $this.val("Error: " + error.responseJSON.message);
        },
      });
    }
  );
});
