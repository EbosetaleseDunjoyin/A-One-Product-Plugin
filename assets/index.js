let notification = (type, title, message) => {
  new Notify({
    status: type,
    title: title,
    text: message,
    effect: "slide",
    speed: 2000,
    showIcon: true,
    showCloseButton: true,
    autoclose: true,
    autotimeout: 5000,
    gap: 20,
    distance: 20,
    type: 1,
    position: "right top",
  });
};

const root_url = wpApiSettings.virtual_staging_root;

jQuery(document).ready(function ($) {
  function getStorageData() {
    let storedData = localStorage.getItem("aiImages");
    // Check if there is any data stored under
    if (storedData) {
      // Convert the retrieved JSON string back to an object
      let aiImages = JSON.parse(storedData);
      // console.log(aiImages[2]);

      var imageContent = "";
      for (let i = aiImages.length - 1; i >= 0; i--) {
        const element = aiImages[i];
        // console.log(element)
        imageContent += `
                        <div class="col-lg-4 col-md-6 col-12">
                          <img src="${element.url}" id="${element.render_id}" alt="" class="image-box">
                          <div class="d-flex justify-content-between  align-items-center">
                              <div>
                                  <a href="${element.url}" class="btn btn-primary px-4 my-3 image-link" download="file.jpg" id="">Download</a>
                              </div>
                              <div>
                                  <button data-id="${element.render_id}" class="btn btn-danger px-4 my-3 delete-link"  id=""><i class="bi bi-trash"></i></button>
                              </div>
                          </div>
                        </div>
                  `;
      }

      // aiImages.forEach(image => {
      //     content += `
      //          <div class="col-lg-4 col-md-6 col-12">
      //             <img src="${image.url}" id="${image.render_id}" alt="" class="image-box">
      //             <a href="${image.url}" class="btn btn-primary px-4 my-3 image-link" download="file.jpg" id="">Download</a>
      //         </div>
      //     `
      // });

      $("#allImages").html(imageContent);
    } else {
      $("#allImages").html("<h4>No Data Found.....</h4>");
      // console.log('No data found in local storage under the key "yourKey".');
    }
  }
  getStorageData();

  function saveData(url, render_id) {
    let storedData = localStorage.getItem("aiImages");
    // Check if there is any data stored under
    if (storedData) {
      let aiImages = JSON.parse(storedData);
      aiImages.push({
        render_id: render_id,
        url: url,
      });
      localStorage.setItem("aiImages", JSON.stringify(aiImages));
    } else {
      let data = JSON.stringify([
        {
          render_id: render_id,
          url: url,
        },
      ]);
      localStorage.setItem("aiImages", data);
    }
  }
  $(".delete-link").each(function () {
    $(this).click(function () {
      // console.log("get")
      let elementId = $(this).attr("data-id");
      let storedData = localStorage.getItem("aiImages");
      let aiImages = JSON.parse(storedData);
      let updatedImages = aiImages.filter(
        (image) => image.render_id !== elementId
      );
      localStorage.setItem("aiImages", JSON.stringify(updatedImages));
      notification("success", "Success", "Image deleted successfully");
      getStorageData();
    });
  });

  $("#deleteImages").click(function (e) {
    if (localStorage.getItem("aiImages")) {
      localStorage.removeItem("aiImages");
      notification("success", "Success", "All images deleted successfully");
    } else {
      notification("error", "Error", "No images found yet");
    }
    getStorageData();
  });

  $("#virtualForm").submit(function (e) {
    e.preventDefault();
    // console.log("grat")
    $("#renderButton").attr("disabled", true);
    let buttonTemp = $("#renderButton").html();
    $("#renderButton").html(
      'Rendring... <div class="spinner-border ms-5" role="status"></div>'
    );
    let file = $("#fileData")[0].files[0];
    // console.log(file);
    let formData = new FormData(this);

    const queryParams = new URLSearchParams(formData).toString();
    // console.log(queryParams);

    formData.append("image", file);

    let xhr = new XMLHttpRequest();
    const proxyUrl = "https://cors-anywhere.herokuapp.com/";

    const apiUrl = `https://api.virtualstagingai.app/v1/render/create?${queryParams}`;
    const pingUrl = "https://api.virtualstagingai.app/v1/ping";

    xhr.open("POST", `${root_url}v1/virtualApi/createRender`, true);
    // xhr.open('POST', proxyUrl + apiUrl, true);
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    // xhr.setRequestHeader('Content-Type', 'multipart/form-data');
    // xhr.setRequestHeader(
    //   "Authorization",
    //   "Api-key vsai-pk-f71d129a-be49-4d49-bf32-39a0e74420d3"
    // );

    xhr.onload = function () {
      if (xhr.status === 200) {
        let response = JSON.parse(xhr.responseText);
        console.log(response);

        if (response.status) {
          // $("#ImageData").removeClass("d-none");
          // $("#imagePort").attr("src", response.result_image_url);
          // $("#downloadImage").attr("href", response.result_image_url);

          // saveData(response.result_image_url, response.render_id);
          // getStorageData();

          notification(
            "success",
            "Success",
            "Your Image rendered successfully"
          );
          $("#virtualForm")[0].reset();
          // $("#downloadImage").attr("download", "rendered-image.jpg");
        }
      } else {
        console.error("Request failed. Status: " + xhr.status);
        notification(
          "error",
          "Error",
          "Rendering failed please reduce the size of the image and try again"
        );
      }
      $("#renderButton").attr("disabled", false);
      $("#renderButton").html(buttonTemp);
    };

    xhr.onerror = function () {
      console.error("Request failed. Network error.");
      $("#renderButton").html(buttonTemp);
      $("#renderButton").attr("disabled", false);
      notification("error", "Error", "Request failed, please try again later");
    };

    xhr.send(formData);
    // xhr.send(JSON.stringify(Object.fromEntries(formData)));
  });
});
