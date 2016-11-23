'use strict';

/* Filters */
// need load the moment.js to use this filter. 
angular.module('app.filters', [])
        .filter('fromNow', function() {
            return function(date) {
                return moment(date).fromNow();
            }
        })

        .filter('fromNow', function() {
            return function(date) {
                return moment(date).fromNow();
            }
        })

        .filter('ellisps', function() {
            return function(value) {
                return value + '...';
            }
        })

        .filter('overWidth', function() {
            return function(date) {
                if (date != '' && date != null) {
                    if (date.length > 4)
                    {
                        return date.substr(0, 4) + '...';
                    } else {
                        return date;
                    }
                }
                return date;

            }
        })

        ;
        
        
