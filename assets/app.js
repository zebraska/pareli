/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// start the Stimulus application
import './bootstrap';

const $ = require('jquery');
// this "modifies" the jquery module: adding behavior to it
// the bootstrap module doesn't export/return anything
window.bootstrap = require('bootstrap');
require('bootstrap-select');
import { Popover } from 'bootstrap';
import { Modal } from 'bootstrap';
import { Tooltip } from 'bootstrap';

$.fn.selectpicker.Constructor.BootstrapVersion = '5';
// or you can include specific pieces
// require('bootstrap/js/dist/tooltip');
// require('bootstrap/js/dist/popover');
var publicPath = 'http://localhost:8000/';

function loadPopovers() {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new Popover(popoverTriggerEl)
    })
}
loadPopovers();

function loadTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new Tooltip(tooltipTriggerEl)
    })
}
loadTooltips();

function loadModals() {
    var modalTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="modal"]'))
    var modalList = modalTriggerList.map(function (modalTriggerEl) {
        return new Modal(modalTriggerEl)
    })
}
loadModals();

function loadSelectpickers() {
    $('select').selectpicker();
}
loadSelectpickers();

//AJAX
//link action
$(document).on('click', '.ajax-link-action', function (event) {
    event.stopImmediatePropagation();
    executeAjaxAction($(this));
});

function executeAjaxAction(element) {
    if (element.attr('data-spinner-type') == 1) {
        document.getElementById(element.attr('data-target-spinner')).innerHTML = '<div class="spinner-grow text-secondary" role="status"><span class="sr-only">Chargement...</span></div>';
    }
    else {
        document.getElementById(element.attr('data-target-spinner')).innerHTML = '<span class="row justify-content-center my-4"><div class="spinner-border text-dark" role="status"><span class="sr-only">Chargement...</span></div></span>';
    }
    $.ajax({
        method: "POST",
        url: element.attr('action'),
        beforeSend: function (jqXHR, settings) {
            jqXHR.url = settings.url;
        },
        success: function (response) {
            window.history.pushState(publicPath + response.bundleName, '', publicPath + response.bundleName);
            for (var rank in response.views) {
                document.getElementById(response.views[rank].target).innerHTML = response.views[rank].view;
            }
            /*if (response.redirectTo != false) {
                console.log('redirect');
                window.location.href = response.redirectTo;
            }*/
            document.getElementById('flash-message').innerHTML = response.flashMessage;
            $(".alert-success").fadeTo(2500, 500).slideUp(500, function () {
                $(".alert-success").slideUp(500);
            });
            loadTooltips();
            loadSelectpickers();
        },
        error: function (jqXHR, error) {
            document.getElementById('flash-message').innerHTML = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>"
                +
                "<p>Une erreur est survenue, merci de communiquer le lien suivant à l'adresse cyril.contant@zebratero.com ou par téléphone au 06 08 43 13 47 :</p>"
                +
                jqXHR.url
                +
                "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
            $(".alert-error").fadeTo(2500, 500);
        }
    });
}


$(document).on('submit', 'form', function (event) {
    event.stopImmediatePropagation();
    executeAjaxFormAction(event);
});

function executeAjaxFormAction(event) {
    event.preventDefault();
    var $form = $(event.currentTarget);
    var btnSubmit = $form.find(':submit');
    var btnSubmitContent = btnSubmit.html();
    document.getElementById('modal-content').innerHTML = '<div class="spinner-grow text-secondary" role="status"><span class="sr-only">Chargement...</span></div>';
    $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: new FormData($form[0]),
        contentType: false,
        processData: false,
        cache: false,
        form: $form[0],
        btnSubmit: btnSubmit,
        btnSubmitContent: btnSubmitContent,
        beforeSend: function (jqXHR, settings) {
            jqXHR.url = settings.url;
        },
        success: function (response) {
            window.history.pushState(publicPath + response.bundleName, '', publicPath + response.bundleName);
            this.btnSubmit.html(this.btnSubmitContent);
            if (response.closeModal) {
                $('.modal-backdrop').hide();
            }
            if (response.redirectTo != false) {
                window.location.href = response.redirectTo;
            }
            this.form.reset();
            for (var rank in response.views) {
                document.getElementById(response.views[rank].target).innerHTML = response.views[rank].view;
            }
            document.getElementById('flash-message').innerHTML = response.flashMessage;
            $(".alert-success").fadeTo(2500, 500).slideUp(500, function () {
                $(".alert-success").slideUp(500);
            });
            $('.selectpicker').selectpicker();
            loadTooltips();
        },
        error: function (jqXHR, error) {
            document.getElementById('flash-message').innerHTML = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>"
                +
                "<p>Une erreur est survenue, merci de communiquer le lien suivant à l'adresse cyril.contant@zebratero.com ou par téléphone au 06 08 43 13 47 :</p>"
                +
                jqXHR.url
                +
                "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
            $(".alert-error").fadeTo(2500, 500);
            const truck_modal = document.querySelector('#supplierModal');
            const modal = bootstrap.Modal.getInstance(truck_modal);
            modal.hide();
        }
    });
}