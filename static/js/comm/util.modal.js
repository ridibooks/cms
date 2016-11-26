/**
 * bootstrap modal 관련 javascript util
 */
define(["jquery", "spin", "comm/common", "bootstrap", "bootstrap.lightbox"], function ($, Spinner, common) {
  "use strict";
  var module = {};
  var instances = {};
  /**
   * Lightbox constructor
   * @param element
   * @constructor
   */
  function Lightbox(element) {
    this.element = $(element);
    this.id = (this.element.attr("id") ? this.element.attr("id") : "lightbox") + ("_" + Math.floor((Math.random() * 1000) + 1));
    this.image = new Image();
    this.src = this.element.prop("src") ? this.element.prop("src") : this.element.data("remote");
  }

  Lightbox.prototype = {
    setTitle: function (image) { //라이트박스 상단 제목
      if (image !== undefined) { //동적으로 이미지 변경
        this.image = image;
      }

      var _title = common.getFileName(this.image.src) + " (" + this.image.width + " X " + this.image.height + ")";
      var _modal = $("#" + this.id);
      var _modalContent = _modal.find(".modal-content").first();
      _modalContent.find(".modal-header").find(".modal-title").html(_title);
    },
    appendDownloadButton: function () {  // 라이트박스 다운로드 버튼 추가한다.
      var _downloadButton =
        $("<a>").addClass("btn btn-default btn-sm").prop("href", this.src).html(
          $("<i>").addClass("glyphicon glyphicon-arrow-down")
        );
      var _modal = $("#" + this.id);
      var _modalContent = _modal.find(".modal-content").first();
      _modalContent.find(".modal-header").find(".modal-title").append(_downloadButton);
    },
    setModalWidth: function () { //라이트 박스 가로 길이 수동 조절한다.
      var _modal = $("#" + this.id);
      var _modalDialog = _modal.find(".modal-dialog").first();
      _modalDialog.css("width", "auto").css("max-width", 600);
    },
    setNavigateButtonHeight: function () { //라이트 박스 네비게이션 버튼 높이 50% 로 고정한다.
      var _modal = $("#" + this.id);
      var _modalBody = _modal.find(".modal-body").first();
      var _lightboxContainer = _modalBody.find('.ekko-lightbox-container').first();
      _lightboxContainer.find("a").css("position", "absolute").css("top", "50%");
    },
    getOptions: function (options) {
      var _this = this;
      var defaultOptions = {
        modal_id: _this.id,
        onContentLoaded: function () {
          _this.setTitle();
          _this.appendDownloadButton();
          _this.setModalWidth();
          _this.setNavigateButtonHeight();
        },
        onNavigate: function (direction, index) {
          if ($.type(index) === "number") {
            var _galleryItemArray = _this.element.parents(options.gallery_parent_selector).first().find('*[data-toggle="lightbox"][data-gallery="' + _this.element.data("gallery") + '"]');
            var _galleryItem = _galleryItemArray.get(index);
            var _image = new Image();
            _image.src = $(_galleryItem).data("remote");
            _this.src = _image.src;
            _this.setTitle(_image);
          }
        }
      };
      return $.extend(defaultOptions, options || {});
    },
    imageLoad: function (options) { //이미지 라이트 박스
      var _this = this;
      _this.image.onload = function () {
        _this.element.ekkoLightbox(_this.getOptions(options));
      };
      this.image.src = this.src;
    }
  };

  /**
   * element 내의 span 태그에 갤러리 박스 이벤트 설정한다.
   * @param element
   * @private
   */
  function _setGalleryBoxHandler(element) {
    element.find("span").each(function () {
      $(this).on("click", function () {
        var lightBox = new Lightbox(this);
        lightBox.imageLoad({gallery_parent_selector: $(element)});
      });
    });
  }

  /**
   * element에 라이트박스 이벤트 설정한다.
   * @param element
   * @private
   */
  function _setLightboxHandler(element) {
    element.addClass("mouseHover");
    element.on("click", function () {
      var lightBox = new Lightbox(this);
      lightBox.imageLoad({remote: lightBox.src});
    });
  }

  /**
   * 갤러리 박스 설정한다.
   */
  module.setGalleryBox = function () {
    for (var i = 0; i < arguments.length; i++) {
      _setGalleryBoxHandler(arguments[i]);
    }
  };

  /**
   * 라이트 박스 설정한다.
   */
  module.setLightbox = function () {
    for (var i = 0; i < arguments.length; i++) {
      _setLightboxHandler(arguments[i]);
    }
  };


  /**
   * 기본 모달 옵션
   * @type {{id: null, onShow: Function, onShown: Function, onHide: Function, onHidden: Function}}
   */
  var defaultModalOption = {
    id: null,
    remote: null,
    backdrop: null,
    title: null,
    footer: null,
    contents: null,
    text: null,
    modalSize: null,
    maxHeight: null,
    opacity: 1.0,
    scroll: false,
    modeless: false,
    onShow: function () {
    },
    onShown: function () {
    },
    onHide: function () {
    },
    onHidden: function () {
    }
  };

  /**
   * 모달 DOM 생성하여 body에 붙인다.
   * @param {ModalDom} modalDom
   * @private
   */
  function _makeModalDom(modalDom) {
    $('#' + modalDom.domId).remove();
    var html = "<div id='" + modalDom.domId + "' class='modal' tabindex='-1' role='dialog' aria-labelledby='dynamicModalLabel' aria-hidden='true'>";
    html += "<div class='modal-dialog'>";
    html += "<div class='modal-content'>";
    html += modalDom.headerDom;
    html += modalDom.bodyDom;
    html += modalDom.footerDom;
    html += "</div></div></div>";
    $(document.body).append(html);
  }

  /**
   * 모달 DOM 객체 singleton
   * @param option
   * @constructor
   */
  function ModalDom(option) {
    if (!option.id) {
      option.id = new Date().getTime();
    }

    if (instances[option.id]) {
      return instances[option.id];
    }

    instances[option.id] = this;
    var _this = this;
    this.options = $.extend(defaultModalOption, option || {});
    this.id = option.id;
    this.domId = 'modal_' + this.options.id;
    this.headerDom = "<div class='modal-header'" + (this.options.title ? "" : " style='display:none'") + "><button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button><h4 class='modal-title' id='dynamicModalLabel'>" + (this.options.title || "&nbsp;") + "</h4></div>";
    this.footerDom = "<div class='modal-footer'" + (this.options.footer ? "" : " style='display:none'") + ">" + this.options.footer + "</div>";
    this.bodyDom = "<div class='modal-body'>" + this.options.contents + "</div>";
    _makeModalDom(this);

    this.modal = $('#' + this.domId);
    //모달 dialog
    this.modalDialog = this.modal.find(".modal-dialog");
    //모달 title
    this.modalTitle = this.modal.find(".modal-title");
    //모달 contents
    this.modalBody = this.modal.find(".modal-body");

    /*
     해당 이벤트가 단 한번만 실행되는 문제가 있어서 $(document).on(event, selector, function)의 형태를 취하도록 함
     https://github.com/jschr/bootstrap-modal/issues/228
     */
    //show 인스턴스 메소드가 호출되는 즉시 실행
    $(document).on("show.bs.modal", this.modal, this.options.onShow.bind(this));
    //모달이 사용자에게 보여졌을때 실행
    $(document).on('shown.bs.modal', this.modal, function () {
      return _this.options.onShown.call(_this);
    });
    //hide 인스턴스 메소드가 호출되는 즉시 실행
    $(document).on('hide.bs.modal', this.modal, this.options.onHide.bind(this));
    //모달이 가려지는게 끝났을때 실행
    $(document).on('hidden.bs.modal', this.modal, function () {
      _this.modal.remove();
      return _this.options.onHidden.call(_this);
    });
  }

  /**
   * 모달 action prototype
   * @type {{show: Function, showMsgOnly: Function, setContents: Function}}
   */
  ModalDom.prototype = {
    show: function () {
      this.modal.modal(this.options);
    },
    hide: function () {
      this.modal.modal("hide");
      delete instances[this.id];
      $('.modal-backdrop').remove();
    },
    setContents: function (contents) {
      this.modalBody.html(contents);
    },
    setTitle: function (title) {
      if (this.options.title !== null) {
        this.modal.find(".modal-header").removeAttr("style");
        this.modalTitle.html(title);
      }
    },
    setScroll: function () {
      if (this.options.scroll === true) {
        this.modalBody.css("overflow-y", "auto");
      }
    },
    setWidth: function(){
      if (common.isEmpty(this.options.modalSize) === false ){
        var _modalDialog = this.modal.find(".modal-dialog").first();
        _modalDialog.addClass(this.options.modalSize);
      }
    },
    setHeight: function(){
      var _height = (this.options.maxHeight === null) ? 700 : this.options.maxHeight;
      var _modalDialog = this.modal.find(".modal-dialog").first();
      _modalDialog.css("height", "auto").css("max-height", _height);
    },
    setOpacity: function() {
      this.modal.css('opacity', this.options.opacity);
    },
    setModeless: function() {
      if (this.options.modeless === true) {
        $('body.modal-open').css('overflow', 'visible');
        this.modal.css('pointer-events', 'none');
      }
    },
    setSpinnerContent: function () {
      var spinnerOptions = $.extend({color: '#337ab7'}, defaultSpinnerOption);
      var spinnerHtml = "<div style='height:120px'>";
      spinnerHtml += "<span style='position: absolute; display: block; top: 50%; left: 50%'>" + new Spinner(spinnerOptions).spin().el.innerHTML + "</span>";
      spinnerHtml += "</div>";

      this.setContents(spinnerHtml);
    }
  };

  /**
   * 모달 보여준다.
   * @param modalOptions
   */
  module.showModal = function (modalOptions) {
    var modal = new ModalDom(modalOptions);
    modal.setContents(modalOptions.contents);
    modal.setTitle(modalOptions.title);
    modal.setScroll();
    modal.setWidth();
    modal.setHeight();
    modal.setOpacity();
    modal.show();

    modal.setModeless();

    return modal;
  };

  /**
   * 기본 spinner option
   * @type {{lines: number, length: number, width: number, radius: number, direction: number, corners: number, trail: number, shadow: boolean, hwaccel: boolean, zIndex: number}}
   * @private
   */
  var defaultSpinnerOption = {
    lines: 13,
    length: 28,
    width: 10,
    radius: 30,
    direction: 1,
    corners: 0.4,
    trail: 60,
    shadow: false,
    hwaccel: false,
    zIndex: 2e9 // The z-index (defaults to 2000000000)
  };

  /**
   * spinner 보여준다.
   */
  module.showSpinner = function () {
    var spinnerOptions = $.extend({color: '#fff'}, defaultSpinnerOption);
    var spinnerHtml = "<div id='spinnerModal' style='background-color: rgba(0, 0, 0, 0.6); width:100%; height:100%; position:fixed; top:0; left:0; z-index:" + (spinnerOptions.zIndex) + ";'>";
    spinnerHtml += "<span style='position: absolute; top: 50%; left: 50%'>" + new Spinner(spinnerOptions).spin().el.innerHTML + "</span>";
    spinnerHtml += "</div>";
    $("body").append(spinnerHtml).css("overflow", "hidden");
  };

  /**
   * spinner 제거한다.
   */
  module.removeSpinner = function () {
    $("#spinnerModal").remove();
    $("body").css("overflow", "");
  };

  return module;
});
