// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * BuyBee repository module to encapsulate all of the AJAX requests that can be sent for BuyBee.
 *
 * @module      profilefield_learningstyles/main
 * @copyright   2024 David Herney @ BambuCo - https://bambuco.co
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';
import Log from 'core/log';
import {get_strings as getStrings} from 'core/str';

// Global variables.
var defaultContainer = '#profilefield_learningstyles-index';
var questionStructure = [];

// Load strings.
var strings = [
    {key: 'info'},
    {key: 'errorsaving', component: 'profilefield_learningstyles'},
];
var s = [];

/**
 * Load strings from server.
 */
function loadStrings() {

    strings.forEach(one => {
        s[one.key] = one.key;
    });

    getStrings(strings).then(function(results) {
        var pos = 0;
        strings.forEach(one => {
            s[one.key] = results[pos];
            pos++;
        });
        return true;
    }).fail(function(e) {
        Log.debug('Error loading strings');
        Log.debug(e);
    });
}
// End of Load strings.

/**
 * Initialize the general controls.
 *
 * @param {object} structureJSON The structure of the test.
 */
export const init = async (structureJSON) => {

    questionStructure = JSON.parse(structureJSON);

    loadStrings();

    var currentpage = 0;
    var currentview = 'fys';
    var $maincontainer = $(defaultContainer);
    var $progressinner = $maincontainer.find('#progressinner');
    var $toleft = $maincontainer.find('#controlbar .toleft');
    var $toright = $maincontainer.find('#controlbar .toright');
    var $viewslistglobal = $maincontainer.find('#viewslistglobal');

    var moving = false;

    var $allpages = [];

    var i = 0;
    $maincontainer.find('#pageslist .onepage').each(function() {
        var $thispage = $(this);

        $allpages.push($thispage);

        if ($thispage.hasClass('active')) {
            currentpage = i;
        }
        i++;

        $thispage.find('.questionoptions button').on('click', function() {
            var $button = $(this);
            var $container = $button.parents('.questionoptions');
            var type = $button.data('type');

            $maincontainer.find('.onepage[data-index="' + $thispage.data('index') + '"] .checked').removeClass('checked');
            $container.find('.option' + type).addClass('checked');

            setTimeout(function() {
                $toright.trigger('click');
            }, 400);

        });
    });

    var getCurrentPage = function() {
        var $current = $allpages[currentpage];
        return $current;
    };

    var changeView = function(viewkey) {
        currentview = viewkey;
        $maincontainer.find('.eachview').hide();
        $maincontainer.find('.eachview[data-view="' + viewkey + '"]').show();

        $viewslistglobal.find('li').removeClass('active');
        $viewslistglobal.find('li[data-changeview="' + viewkey + '"]').addClass('active');

    };

    var setBoard = function() {

        var $current = getCurrentPage();

        if ($current.data('type') == 'info') {
            $maincontainer.find('#testfooter').hide();
            $viewslistglobal.hide();
        } else {
            $maincontainer.find('#testfooter').show();
            $viewslistglobal.show();
        }

        if (currentpage == 0) {
            $toleft.hide();
        } else {
            $toleft.show();
        }

        if ((currentpage + 1) >= $allpages.length) {
            $toright.hide();
        } else {
            if ($current.find('.testquestion').length == 0 || $current.find('.questionoptions .checked').length > 0) {
                $toright.show();
            } else {
                $toright.hide();
            }
        }

        var percent = currentpage * 100 / $allpages.length;
        $progressinner.css('width', percent + '%');

        if ($current.find('.test-results, .stylefeedback').length > 0) {
            drawResults(defaultContainer);
        }

    };

    $toleft.on('click', function(e) {
        e.preventDefault();

        if (currentpage == 0 || moving) {
            return;
        }

        moving = true;

        var $current = getCurrentPage();

        $current.hide(200, function() {
            currentpage--;
            let $current = getCurrentPage();
            $current.show(200, function() {
                moving = false;
            });
            setBoard();
        });

    });

    $toright.on('click', function(e) {
        e.preventDefault();

        if ((currentpage + 1) == $allpages.length || moving) {
            return;
        }

        var $current = getCurrentPage();

        moving = true;
        $current.hide(200, function() {
            currentpage++;
            $current = getCurrentPage();
            $current.show(200, function() {
                moving = false;
            });
            setBoard();
        });
    });

    $maincontainer.find('[data-go]').on('click', function(e) {
        e.preventDefault();

        var action = $(this).data('go');
        switch (action) {
            case 'next':
                $toright.trigger('click');
                break;
            case 'previous':
                $toleft.trigger('click');
                break;
            default:
                if (!isNaN(action) && !isNaN(parseInt(action))) {
                    moving = true;
                    var $current = getCurrentPage();
                    $current.hide(200, function() {
                        // Change the current page.
                        currentpage = parseInt(action);
                        $current = getCurrentPage();
                        $current.show(200, function() {
                            moving = false;
                        });
                        setBoard();
                    });
                }
        }
    });

    $maincontainer.find('.test-btns-save[data-sesskey]').on('click', function() {

        var answers = getAnswers();
        var sesskey = $(this).data('sesskey');

        saveInfo(answers, sesskey, function() { $toright.trigger('click'); });

    });

    $maincontainer.find('[data-changeview]').on('click', function(e) {
        e.preventDefault();

        var viewkey = $(this).data('changeview');
        changeView(viewkey);
    });

    setBoard();
    changeView(currentview);
};

/**
 * Show a message in a modal.
 *
 * @param {string} message
 * @param {string} type The message type: info, help.
 * @param {function} callback
 * @returns
 */
var showMessage = (message, type = 'info', callback = null) => {

    let modalTitle = '';
    let content = message;

    switch (type) {
        case 'info':
            modalTitle = s.info;
            content = '<p>' + message + '</p>';
        break;
        case 'help':
            modalTitle = '';
            content = '<div class="test-help">' + message.html() + '</div>';
            break;
        }

    return ModalFactory.create({
        type: ModalFactory.types.CANCEL,
        body: content,
        title: modalTitle
    })
    .then(function(modal) {

        modal.setButtonText('cancel', 'Ok');

        // When the dialog is closed, perform the callback (if provided).
        modal.getRoot().on(ModalEvents.hidden, function() {
            if (callback) {
                callback();
            }
            modal.getRoot().remove();
        });

        modal.show();

        return modal;
    });
};

/**
 * Save the user answers.
 *
 * @param {Array} answers The answers of the user.
 * @param {string} sesskey The current sesskey.
 * @param {function} callback The callback function if the save is successful.
 * @returns {Promise} The promise of the AJAX call.
 * @throws {Error} If the AJAX call fails.
 */
var saveInfo = (answers, sesskey, callback = null) => {
    answers = JSON.stringify(answers);
    return Ajax.call([{
        methodname: 'profilefield_learningstyles_save',
        args: { "answers": answers, "sesskey": sesskey },
        done: function(data) {
            if (data) {
                if (callback) {
                    callback();
                }
            } else {
                showMessage(s.errorsaving);
            }
        },
        fail: function (e) {
            showMessage(e.message);
        }
    }]);
};

/**
 * Calculate the affinity of each style.
 *
 * @param {string} maincontainer CSS selector of the main container.
 */
var drawResults = (maincontainer) => {
    if (!maincontainer || maincontainer == '') {
        maincontainer = defaultContainer;
    }

    var $maincontainer = $(maincontainer);
    var $container = $maincontainer.find('.test-results');

    calcAffinity($maincontainer);

    questionStructure.forEach(group => {
        let $bar = $container.find('[data-style="' + group.key + '"] .bar');
        let $indicator = $bar.find('> div');
        let percent = (group.affinity + 11) * 100 / 22;
        let barwidth = $bar.width();
        let left = (barwidth - barwidth / 12) * percent / 100;
        $indicator.css('left', left + 'px');

        group.styles.forEach((style) => {
            let affinity = Math.abs(group.affinity);
            let level = affinity < 4 ? 3 : (affinity < 8 ? 2 : 1);

            $maincontainer.find('.stylefeedback[data-style="' + style + '"] [data-level="' + level + '"]').show();
        });
    });
};

/**
 *
 * @param {string} maincontainer The CSS selector for the main container.
 * @returns {Array} The answers of the user.
 */
var getAnswers = (maincontainer) => {
    if (!maincontainer || maincontainer == '') {
        maincontainer = defaultContainer;
    }

    var $maincontainer = $(maincontainer);
    var answers = [];

    $maincontainer.find('.onepage[data-type="style"]').each(function() {
        let $stylepage = $(this);
        let $checked = $stylepage.find('.questionoptions > .checked');
        answers.push({
            'index': $stylepage.data('index'),
            'view': $checked.parents('.eachview').data('view'),
            'value': $checked.find('button[data-type]').data('type')
        });
    });

    return answers;
};

/**
 * Calc the user affinity with each style.
 *
 * @param {object} $maincontainer JQuery object of the main container.
 *
 */
var calcAffinity = ($maincontainer) => {
    var answers = getAnswers($maincontainer);

    questionStructure.forEach(group => {
        var checkeda = 0;
        var checkedb = 0;
        group.questions.forEach(index => {
            var answer = answers.find(item => item.index == index);

            if (typeof answer !== 'undefined' && answer.value !== 'undefined') {
                if (answer.value == 'a') {
                    checkeda++;
                } else {
                    checkedb++;
                }
            }
        });

        group.affinity = checkedb - checkeda;
    });

};
