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

var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
  return new Popover(popoverTriggerEl)
})

var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new Tooltip(tooltipTriggerEl)
})

var modalTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="modal"]'))
var modalList = modalTriggerList.map(function (modalTriggerEl) {
  return new Modal(modalTriggerEl)
})

$(function () {
  $('select').selectpicker();
});


