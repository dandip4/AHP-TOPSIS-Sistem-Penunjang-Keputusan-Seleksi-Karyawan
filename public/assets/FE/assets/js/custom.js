(function($){
    "use strict";


        $(window).on('load', function (){

            /*----------------------------------------------------*/
            /*	Modal Window
            /*----------------------------------------------------*/

            setTimeout(function () {
                $(".modal:not(.auto-off)").modal("show");
            },3600);

            $('#preloader').delay(350).fadeOut('slow');
            $('body').delay(350).css({ 'overflow': 'visible' });
        })

        new WOW().init();

        /*---- Bottom To Top Scroll Script ---*/
        $(window).on('scroll', function() {
            var height = $(window).scrollTop();
            if (height > 100) {
                $('#back2Top').fadeIn();
            } else {
                $('#back2Top').fadeOut();
            }
        });


        // Tooltip
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        $("#back2Top").on('click', function(event) {
            event.preventDefault();
            $("html, body").animate({ scrollTop: 0 }, "slow");
            return false;
        });

        // Navigation
        ! function(n, e, i, a) {
            n.navigation = function(t, s) {
                var o = {
                        responsive: !0,
                        mobileBreakpoint:992,
                        showDuration: 300,
                        hideDuration: 300,
                        showDelayDuration: 0,
                        hideDelayDuration: 0,
                        submenuTrigger: "hover",
                        effect: "fade",
                        submenuIndicator: !0,
                        hideSubWhenGoOut: !0,
                        visibleSubmenusOnMobile: !1,
                        fixed: !1,
                        overlay: !0,
                        overlayColor: "rgba(0, 0, 0, 0.5)",
                        hidden: !1,
                        offCanvasSide: "left",
                        onInit: function() {},
                        onShowOffCanvas: function() {},
                        onHideOffCanvas: function() {}
                    },
                    u = this,
                    r = Number.MAX_VALUE,
                    d = 1,
                    f = "click.nav touchstart.nav",
                    l = "mouseenter.nav",
                    c = "mouseleave.nav";
                u.settings = {};
                var t = (n(t), t);
                n(t).find(".nav-menus-wrapper").prepend("<span class='nav-menus-wrapper-close-button'>✕</span>"), n(t).find(".nav-search").length > 0 && n(t).find(".nav-search").find("form").prepend("<span class='nav-search-close-button'>✕</span>"), u.init = function() {
                    u.settings = n.extend({}, o, s), "right" == u.settings.offCanvasSide && n(t).find(".nav-menus-wrapper").addClass("nav-menus-wrapper-right"), u.settings.hidden && (n(t).addClass("navigation-hidden"), u.settings.mobileBreakpoint = 99999), v(), u.settings.fixed && n(t).addClass("navigation-fixed"), n(t).find(".nav-toggle").on("click touchstart", function(n) {
                        n.stopPropagation(), n.preventDefault(), u.showOffcanvas(), s !== a && u.callback("onShowOffCanvas")
                    }), n(t).find(".nav-menus-wrapper-close-button").on("click touchstart", function() {
                        u.hideOffcanvas(), s !== a && u.callback("onHideOffCanvas")
                    }), n(t).find(".nav-search-button").on("click touchstart", function(n) {
                        n.stopPropagation(), n.preventDefault(), u.toggleSearch()
                    }), n(t).find(".nav-search-close-button").on("click touchstart", function() {
                        u.toggleSearch()
                    }), n(t).find(".megamenu-tabs").length > 0 && y(), n(e).resize(function() {
                        m(), C()
                    }), m(), s !== a && u.callback("onInit")
                };
                var v = function() {
                    n(t).find("li").each(function() {
                        n(this).children(".nav-dropdown,.megamenu-panel").length > 0 && (n(this).children(".nav-dropdown,.megamenu-panel").addClass("nav-submenu"), u.settings.submenuIndicator && n(this).children("a").append("<span class='submenu-indicator'><span class='submenu-indicator-chevron'></span></span>"))
                    })
                };
                u.showSubmenu = function(e, i) {
                    g() > u.settings.mobileBreakpoint && n(t).find(".nav-search").find("form").slideUp(), "fade" == i ? n(e).children(".nav-submenu").stop(!0, !0).delay(u.settings.showDelayDuration).fadeIn(u.settings.showDuration) : n(e).children(".nav-submenu").stop(!0, !0).delay(u.settings.showDelayDuration).slideDown(u.settings.showDuration), n(e).addClass("nav-submenu-open")
                }, u.hideSubmenu = function(e, i) {
                    "fade" == i ? n(e).find(".nav-submenu").stop(!0, !0).delay(u.settings.hideDelayDuration).fadeOut(u.settings.hideDuration) : n(e).find(".nav-submenu").stop(!0, !0).delay(u.settings.hideDelayDuration).slideUp(u.settings.hideDuration), n(e).removeClass("nav-submenu-open").find(".nav-submenu-open").removeClass("nav-submenu-open")
                };
                var h = function() {
                        n("body").addClass("no-scroll"), u.settings.overlay && (n(t).append("<div class='nav-overlay-panel'></div>"), n(t).find(".nav-overlay-panel").css("background-color", u.settings.overlayColor).fadeIn(300).on("click touchstart", function(n) {
                            u.hideOffcanvas()
                        }))
                    },
                    p = function() {
                        n("body").removeClass("no-scroll"), u.settings.overlay && n(t).find(".nav-overlay-panel").fadeOut(400, function() {
                            n(this).remove()
                        })
                    };
                u.showOffcanvas = function() {
                    h(), "left" == u.settings.offCanvasSide ? n(t).find(".nav-menus-wrapper").css("transition-property", "left").addClass("nav-menus-wrapper-open") : n(t).find(".nav-menus-wrapper").css("transition-property", "right").addClass("nav-menus-wrapper-open")
                }, u.hideOffcanvas = function() {
                    n(t).find(".nav-menus-wrapper").removeClass("nav-menus-wrapper-open").on("webkitTransitionEnd moztransitionend transitionend oTransitionEnd", function() {
                        n(t).find(".nav-menus-wrapper").css("transition-property", "none").off()
                    }), p()
                }, u.toggleOffcanvas = function() {
                    g() <= u.settings.mobileBreakpoint && (n(t).find(".nav-menus-wrapper").hasClass("nav-menus-wrapper-open") ? (u.hideOffcanvas(), s !== a && u.callback("onHideOffCanvas")) : (u.showOffcanvas(), s !== a && u.callback("onShowOffCanvas")))
                }, u.toggleSearch = function() {
                    "none" == n(t).find(".nav-search").find("form").css("display") ? (n(t).find(".nav-search").find("form").slideDown(), n(t).find(".nav-submenu").fadeOut(200)) : n(t).find(".nav-search").find("form").slideUp()
                };
                var m = function() {
                        u.settings.responsive ? (g() <= u.settings.mobileBreakpoint && r > u.settings.mobileBreakpoint && (n(t).addClass("navigation-portrait").removeClass("navigation-landscape"), D()), g() > u.settings.mobileBreakpoint && d <= u.settings.mobileBreakpoint && (n(t).addClass("navigation-landscape").removeClass("navigation-portrait"), k(), p(), u.hideOffcanvas()), r = g(), d = g()) : k()
                    },
                    b = function() {
                        n("body").on("click.body touchstart.body", function(e) {
                            0 === n(e.target).closest(".navigation").length && (n(t).find(".nav-submenu").fadeOut(), n(t).find(".nav-submenu-open").removeClass("nav-submenu-open"), n(t).find(".nav-search").find("form").slideUp())
                        })
                    },
                    g = function() {
                        return e.innerWidth || i.documentElement.clientWidth || i.body.clientWidth
                    },
                    w = function() {
                        n(t).find(".nav-menu").find("li, a").off(f).off(l).off(c)
                    },
                    C = function() {
                        if (g() > u.settings.mobileBreakpoint) {
                            var e = n(t).outerWidth(!0);
                            n(t).find(".nav-menu").children("li").children(".nav-submenu").each(function() {
                                n(this).parent().position().left + n(this).outerWidth() > e ? n(this).css("right", 0) : n(this).css("right", "auto")
                            })
                        }
                    },
                    y = function() {
                        function e(e) {
                            var i = n(e).children(".megamenu-tabs-nav").children("li"),
                                a = n(e).children(".megamenu-tabs-pane");
                            n(i).on("click.tabs touchstart.tabs", function(e) {
                                e.stopPropagation(), e.preventDefault(), n(i).removeClass("active"), n(this).addClass("active"), n(a).hide(0).removeClass("active"), n(a[n(this).index()]).show(0).addClass("active")
                            })
                        }
                        if (n(t).find(".megamenu-tabs").length > 0)
                            for (var i = n(t).find(".megamenu-tabs"), a = 0; a < i.length; a++) e(i[a])
                    },
                    k = function() {
                        w(), n(t).find(".nav-submenu").hide(0), navigator.userAgent.match(/Mobi/i) || navigator.maxTouchPoints > 0 || "click" == u.settings.submenuTrigger ? n(t).find(".nav-menu, .nav-dropdown").children("li").children("a").on(f, function(i) {
                            if (u.hideSubmenu(n(this).parent("li").siblings("li"), u.settings.effect), n(this).closest(".nav-menu").siblings(".nav-menu").find(".nav-submenu").fadeOut(u.settings.hideDuration), n(this).siblings(".nav-submenu").length > 0) {
                                if (i.stopPropagation(), i.preventDefault(), "none" == n(this).siblings(".nav-submenu").css("display")) return u.showSubmenu(n(this).parent("li"), u.settings.effect), C(), !1;
                                if (u.hideSubmenu(n(this).parent("li"), u.settings.effect), "_blank" == n(this).attr("target") || "blank" == n(this).attr("target")) e.open(n(this).attr("href"));
                                else {
                                    if ("#" == n(this).attr("href") || "" == n(this).attr("href")) return !1;
                                    e.location.href = n(this).attr("href")
                                }
                            }
                        }) : n(t).find(".nav-menu").find("li").on(l, function() {
                            u.showSubmenu(this, u.settings.effect), C()
                        }).on(c, function() {
                            u.hideSubmenu(this, u.settings.effect)
                        }), u.settings.hideSubWhenGoOut && b()
                    },
                    D = function() {
                        w(), n(t).find(".nav-submenu").hide(0), u.settings.visibleSubmenusOnMobile ? n(t).find(".nav-submenu").show(0) : (n(t).find(".nav-submenu").hide(0), n(t).find(".submenu-indicator").removeClass("submenu-indicator-up"), u.settings.submenuIndicator ? n(t).find(".submenu-indicator").on(f, function(e) {
                            return e.stopPropagation(), e.preventDefault(), u.hideSubmenu(n(this).parent("a").parent("li").siblings("li"), "slide"), u.hideSubmenu(n(this).closest(".nav-menu").siblings(".nav-menu").children("li"), "slide"), "none" == n(this).parent("a").siblings(".nav-submenu").css("display") ? (n(this).addClass("submenu-indicator-up"), n(this).parent("a").parent("li").siblings("li").find(".submenu-indicator").removeClass("submenu-indicator-up"), n(this).closest(".nav-menu").siblings(".nav-menu").find(".submenu-indicator").removeClass("submenu-indicator-up"), u.showSubmenu(n(this).parent("a").parent("li"), "slide"), !1) : (n(this).parent("a").parent("li").find(".submenu-indicator").removeClass("submenu-indicator-up"), void u.hideSubmenu(n(this).parent("a").parent("li"), "slide"))
                        }) : k())
                    };
                u.callback = function(n) {
                    s[n] !== a && s[n].call(t)
                }, u.init()
            }, n.fn.navigation = function(e) {
                return this.each(function() {
                    if (a === n(this).data("navigation")) {
                        var i = new n.navigation(this, e);
                        n(this).data("navigation", i)
                    }
                })
            }
        }
        (jQuery, window, document), $(document).ready(function() {
            $("#navigation").navigation()
        });

        $(window).scroll(function() {
            var scroll = $(window).scrollTop();

            if (scroll >= 50) {
                $(".header").addClass("header-fixed");
            } else {
                $(".header").removeClass("header-fixed");
            }
        });

    // Brand Slide
    $('#brand-slide').slick({
        slidesToShow: 7,
        centerMode: false,
        centerPadding: '20px',
        infinite: true,
        speed: 700,
        arrows: false,
        autoplay: true,
        autoplaySpeed: 3000,
        pauseOnHover: false,
        pauseOnFocus: false,
        slidesToScroll: 1,
        responsive: [
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 5
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 3
                }
            }
        ]
    });

    // Our Testimonials
    $('#testimonials-slide').slick({
        slidesToShow: 4,
        dots: true,
        infinite: true,
        speed: 700,
        arrows: false,
        autoplay: true,
        autoplaySpeed: 3000,
        pauseOnHover: false,
        pauseOnFocus: false,
        slidesToScroll: 1,
        responsive: [
            {
                breakpoint: 993,
                settings: {
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 767,
                settings: {
                    slidesToShow: 1
                }
            }
        ]
    });

    // modal
    document.addEventListener("DOMContentLoaded", function () {
        // Ambil elemen modal
        const modal = document.getElementById("customModal");
        const modalTitle = document.getElementById("modalTitle");
        const modalImage = document.getElementById("modalImage");
        const modalDescription = document.getElementById("modalDescription");
        const closeModal = document.querySelector(".close-modal");

        // Tangani klik tombol "Selengkapnya"
        document.querySelectorAll(".btn-open-modal").forEach(button => {
            button.addEventListener("click", function () {
                const title = this.getAttribute("data-title");
                const img = this.getAttribute("data-img");
                const description = this.getAttribute("data-description");

                modalTitle.textContent = title;
                modalImage.src = img;
                modalDescription.textContent = description;

                modal.classList.add("show"); // Tampilkan modal
            });
        });

        // Tangani klik tombol close modal
        if (closeModal) { // Ensure closeModal exists
            closeModal.addEventListener("click", function () {
                modal.classList.remove("show");
            });
        }

        // Tutup modal jika klik di luar modal
        window.addEventListener("click", function (e) {
            if (e.target === modal) {
                modal.classList.remove("show");
            }
        });
    });


    // modal gallery
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById('customGalleryModal');
        const modalContent = document.getElementById('customModalContent');
        const modalTitle = document.getElementById('customGalleryModalLabel');
        const closeButton = document.querySelector('.custom-close');

        // When a gallery title is clicked
        document.querySelectorAll('.custom-gallery-trigger').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault(); // Prevent default button behavior

                const title = this.getAttribute('data-title');
                const file = this.getAttribute('data-file');
                const type = this.getAttribute('data-type');

                modalContent.innerHTML = ''; // Clear any previous content

                if (type === 'video') {
                    // Extract YouTube video ID from URL
                    const videoId = file.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\n?#]+)/);
                    if (videoId) {
                        const iframe = document.createElement('iframe');
                        iframe.setAttribute('src', `https://www.youtube.com/embed/${videoId[1]}`);
                        iframe.setAttribute('width', '100%');
                        iframe.setAttribute('height', '500');
                        iframe.setAttribute('frameborder', '0');
                        iframe.setAttribute('allowfullscreen', 'true');
                        iframe.style.aspectRatio = '16/9';
                        iframe.classList.add('img-fluid', 'rounded');
                        modalContent.appendChild(iframe);
                    } else {
                        // Fallback for non-YouTube URLs
                        const videoElement = document.createElement('video');
                        videoElement.setAttribute('controls', 'true');
                        videoElement.classList.add('img-fluid');
                        const source = document.createElement('source');
                        source.setAttribute('src', file);
                        source.setAttribute('type', 'video/mp4');
                        videoElement.appendChild(source);
                        modalContent.appendChild(videoElement);
                    }
                } else {
                    const imageElement = document.createElement('img');
                    imageElement.setAttribute('src', file);
                    imageElement.setAttribute('class', 'img-fluid rounded shadow');
                    modalContent.appendChild(imageElement);
                }

                modalTitle.textContent = title; // Set the modal title
                modal.style.display = 'block'; // Show the modal
            });
        });

        // Close the modal
        closeButton.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        // Close the modal if the user clicks outside the modal content
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });

    // modal aduan
    document.addEventListener("DOMContentLoaded", function() {
        var modal = document.getElementById('detailModal');
        var closeBtn = document.querySelector('.aduan-close-btn');

            document.querySelectorAll('.btn-detail').forEach(function(button) {
            button.addEventListener('click', function(event) {
                document.getElementById('modalKategori').textContent = button.getAttribute('data-kategori');
                document.getElementById('modalIsi').textContent = button.getAttribute('data-isi');
                document.getElementById('modalNama').textContent = button.getAttribute('data-nama');
                document.getElementById('modalNoTelp').textContent = button.getAttribute('data-no-telp');
                document.getElementById('modalTanggal').textContent = button.getAttribute('data-tanggal');

                // Handle image display
                const gambarSrc = button.getAttribute('data-gambar');
                const gambarContainer = document.getElementById('modalGambarContainer');
                const gambarElement = document.getElementById('modalGambar');

                if (gambarSrc && gambarSrc !== 'null') {
                    gambarElement.src = gambarSrc;
                    gambarContainer.style.display = 'block';
                } else {
                    gambarContainer.style.display = 'none';
                }

                // Handle status display
                const status = button.getAttribute('data-status');
                const statusElement = document.getElementById('modalStatus');
                if (status === 'proses') {
                    statusElement.innerHTML = '<span class="badge bg-warning text-dark">Diproses</span>';
                } else if (status === 'selesai') {
                    statusElement.innerHTML = '<span class="badge bg-success">Selesai</span>';
                } else if (status === 'tolak') {
                    statusElement.innerHTML = '<span class="badge bg-danger">Ditolak</span>';
                } else {
                    statusElement.innerHTML = '<span class="badge bg-secondary">Belum Direspon</span>';
                }

                document.getElementById('modalRespon').innerText = this.dataset.respon || "Belum ada respon";

                var defaultImage = document.querySelector('#modalGambar').getAttribute('data-default');
                var imgSrc = button.getAttribute('data-gambar');
                document.getElementById('modalGambar').src = imgSrc && imgSrc.trim() ? imgSrc : defaultImage;


                modal.style.display = "block";
            });
        });

        closeBtn.addEventListener('click', function() {
            modal.style.display = "none";
        });

        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        });
    });


    // Hero Banner Slider
    $('#hero-slider').slick({
        slidesToShow: 1,
        dots: true,
        infinite: true,
        speed: 700,
        arrows: false,
        autoplay: true,
        autoplaySpeed: 3000,
        pauseOnHover: false,
        pauseOnFocus: false,
        slidesToScroll: 1,
        responsive: [
            {
                breakpoint: 767,
                settings: {
                    slidesToShow: 1
                }
            }
        ]
    });

    // Single Reviews
    $('#single-reviews').slick({
        slidesToShow: 1,
        dots: true,
        infinite: true,
        speed: 700,
        arrows: false,
        autoplay: true,
        autoplaySpeed: 3000,
        pauseOnHover: false,
        pauseOnFocus: false,
        slidesToScroll: 1
    });

    // Product Slide
    $('.product-slide').slick({
        slidesToShow: 1,
        infinite: true,
        speed: 700,
        arrows: true,
        autoplay: true,
        autoplaySpeed: 3000,
        pauseOnHover: false,
        pauseOnFocus: false,
        slidesToScroll: 1
    });

    // Real Estate Slide
    $('.realestate-slide').slick({
        slidesToShow: 1,
        dots: true,
        infinite: true,
        speed: 400,
        arrows: false,
        autoplay: true,
        autoplaySpeed: 3000,
        pauseOnHover: false,
        pauseOnFocus: false,
        slidesToScroll: 1
    });

    // Pastikan semua slider telah di-load dengan baik
    $('#brand-slide, #testimonials-slide, #hero-slider, #single-reviews, .product-slide, .realestate-slide').slick('setPosition');

    // MagnificPopup (untuk gallery popup)
    $('body').magnificPopup({
        type: 'image',
        delegate: 'a.mfp-gallery',
        fixedContentPos: true,
        fixedBgPos: true,
        overflowY: 'auto',
        closeBtnInside: false,
        preloader: true,
        removalDelay: 0,
        mainClass: 'mfp-fade',
        gallery: {
            enabled: true
        }
    });


        $("body").on('click', '.toggle-password', function() {
          $(this).toggleClass("fa-eye-slash");
          var input = $("#password-field");
          if (input.attr("type") === "password") {
            input.attr("type", "text");
          } else {
            input.attr("type", "password");
          }

        });

        (function () {
            var n,
                o = document.querySelectorAll(".masonry-grid");
            if (null !== o)
                for (var e = 0; e < o.length; e++) {
                    var t = (function (e) {
                        (n = new Shuffle(o[e], { itemSelector: ".masonry-grid-item", sizer: ".masonry-grid-item" })),
                            imagesLoaded(o[e]).on("progress", function () {
                                n.layout();
                            });
                        var a = o[e].closest(".masonry-filterable");
                        if (null === a) return { v: void 0 };
                        for (var t = a.querySelectorAll(".masonry-filters [data-group]"), r = 0; r < t.length; r++)
                            t[r].addEventListener("click", function (e) {
                                var t = a.querySelector(".masonry-filters .active"),
                                    r = this.dataset.group;
                                null !== t && t.classList.remove("active"), this.classList.add("active"), n.filter(r), e.preventDefault();
                            });
                    })(e);

                }
        })()

        // Range Slider Script
        $(".js-range-slider").ionRangeSlider({
            type: "single",
            min: 0,
            max: 1000,
            from: 200,
            to: 500,
            grid: true
        });


    // ------------------ End Document ------------------ //

    })(this.jQuery);
