(function (module) {

  'use strict';

  // # Globbing
  // for performance reasons we're only matching one level down:
  // 'test/spec/{,*/}*.js'
  // use this if you want to match all subfolders:
  // 'test/spec/**/*.js'

  module.exports = function (grunt) {
    // load all grunt tasks
    grunt.loadNpmTasks('grunt-contrib-connect');
    grunt.loadNpmTasks('grunt-contrib-connect');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-qunit');

    grunt.initConfig({
      connect: {
        options: {
          port: 8000
        },
        server: {
        }
      },
      open: {
        server: {
          path: 'http://localhost:<%= connect.options.port %>'
        }
      },
      clean: {
         server: '.tmp'
      },
      jshint: {
        /*options: {
          jshintrc: '.jshintrc'
        },*/
        all: [
          'Gruntfile.js',
          'test/tests.js'
        ]
      },
      qunit: {
        all: {
          options: {
            urls: [
              'http://localhost:<%= connect.options.port %>/test/index.html'
            ]
          }
        }
      }
    });

    grunt.registerTask('test', [
      'clean:server',
      'connect',
      'qunit'
    ]);

    grunt.registerTask('default', [
      'jshint',
      'test'
    ]);
  };

}(module));