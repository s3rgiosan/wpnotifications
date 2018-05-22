/* global module require process */
module.exports = function(grunt) {
  var path = require("path");

  require("load-grunt-config")(grunt, {
    configPath: path.join(process.cwd(), "grunt/config"),
    jitGrunt: {
      customTasksDir: "grunt/tasks",
      staticMappings: {
        makepot: "grunt-wp-i18n"
      }
    },
    data: {
      i18n: {
        author: "Sérgio Santos <me@s3rgiosan.com>",
        support: "https://github.com/s3rgiosan/wpnotifications",
        pluginSlug: "wpnotifications",
        mainFile: "wpnotifications",
        textDomain: "wpnotifications",
        potFilename: "wpnotifications"
      },
      badges: {
        packagist_stable: "",
        packagist_downloads: "",
        packagist_license: "",
        codacy_grade: "",
        codeclimate_grade: ""
      }
    }
  });
};
