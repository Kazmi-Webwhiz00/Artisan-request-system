jQuery(function ($) {
  // Toggle password visibility
  $(".artisan-toggle-pass").on("click", function () {
    var $input = $(this).siblings("input");
    var isPwd = $input.attr("type") === "password";
    $input.attr("type", isPwd ? "text" : "password");
    $(this).toggleClass("visible");
  });

  // AJAX login
  $(".artisan-login-form").on("submit", function (e) {
    e.preventDefault();

    var data = $(this).serializeArray();
    data.push({ name: "action", value: "custom_ajax_login" });

    $.post(ArtisanLogin.ajax_url, data)
      .done(function (res) {
        // … inside your AJAX .done() …
        if (res.success) {
          Swal.fire({
            icon: "success",
            showCloseButton: true,
            showConfirmButton: false,
            customClass: {
              popup: "swal2-custom-popup",
              icon: "swal2-custom-icon",
              closeButton: "swal2-custom-close",
            },
            html: `
      <div class="swal2-message-wrapper">
        <div class="swal2-custom-title">Woohoo!</div>
        <div class="swal2-custom-content">Successfully logged in</div>
      </div>
    `,
            timer: 3000, // Time in milliseconds (5000ms = 5 seconds)
            timerProgressBar: true, // Optional: Show progress bar while timer is running
          }).then(function () {
            var currentUrl = ArtisanLogin.current_url;
            if (currentUrl.includes("/login")) {
              // Remove "/login" from the URL
              var newUrl = currentUrl.replace("/login", "");

              // Ensure the redirection is correct by only appending the relative path
              var redirectUrl = ArtisanLogin.redirect_url;

              // Check if the current URL already has the domain part, to avoid adding it twice
              if (redirectUrl.startsWith("/")) {
                console.log("::Redirect URL is relative", newUrl + redirectUrl);
                // If ArtisanLogin.redirect_url is a relative URL, append it correctly
                window.location.href = newUrl + redirectUrl;
              } else {
                // If ArtisanLogin.redirect_url already contains the full URL, redirect as is
                window.location.href = redirectUrl;
              }
            } else {
              // If not on the login page, reload the current page
              window.location.href = currentUrl;
            }
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Oops!",
            // render our server-sent HTML (strong, a, etc.)
            html: res.data,
            confirmButtonText: "OK",
            // optional: apply your own wrapper classes for further styling
            customClass: {
              popup: "swal2-artisan-popup",
              title: "swal2-artisan-title",
              htmlContainer: "swal2-artisan-html",
              confirmButton: "swal2-artisan-button",
            },
          });
        }
      })
      .fail(function () {
        Swal.fire("Error", "Could not reach server.", "error");
      });
  });

  var currentUrl = window.location.href;

  // Get the link element by its ID
  var logoLink = document.getElementById("logo-link");

  // If the current URL contains "/login", modify the href
  if (currentUrl.includes("/login")) {
    // Remove "/login" from the URL
    var newUrl = currentUrl.replace("/login", "");

    // Update the href attribute of the logo link to the new URL
    logoLink.href = newUrl;
  } else {
    // If not on the login page, set it to the base URL
    logoLink.href = window.location.origin; // This will set it to the base URL (e.g., https://actieservice.nl)
  }

  var signUpLink = document.querySelector(".artisan-signup-link");

  // If the link exists, add the click event
  if (signUpLink) {
    signUpLink.addEventListener("click", function (event) {
      event.preventDefault(); // Prevent the default link behavior

      // Check if the current URL contains "/login" and replace it with "/register"
      if (currentUrl.includes("/login")) {
        var newUrl = currentUrl.replace("login", ArtisanLogin.signup_url);

        // Redirect to the new URL with "/register"
        window.location.href = newUrl;
      } else {
        // If there's no "/login" slug in the current URL, stay on the page (optional behavior)
        window.location.href = currentUrl.replace(
          "/login",
          ArtisanLogin.signup_url
        );
      }
    });
  }
});
