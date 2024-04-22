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
 * @module      profilefield_learningstyles/api
 * @copyright   2024 David Herney @ BambuCo - https://bambuco.co
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Log from 'core/log';

/**
 * Initialize the API.
 *
 */
export const init = async () => {

    var API = {
        getLS: getLS
    };

    return API;
};

/**
 * Get the user current styles.
 * This function is created as API for other modules.
 *
 * @param {function} callback The callback function to set the LS.
 * @returns {Promise} The promise of the AJAX call.
 * @throws {Error} If the AJAX call fails.
 */
var getLS = (callback = null) => {

    return Ajax.call([{
        methodname: 'profilefield_learningstyles_get',
        done: function(data) {
            callback(data);
        },
        fail: function (e) {
            Log.error(e);
            callback(null);
        }
    }]);
};
