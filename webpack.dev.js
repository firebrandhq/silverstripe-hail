const merge = require('webpack-merge');
const common = require('./webpack.common.js');
const devOptions = {
    mode: 'development',
    devtool: 'source-map'
};

let fullConfig = [];
if (Array.isArray(common)) {
    common.forEach(function (conf) {
        fullConfig.push(
            merge(conf, devOptions)
        );
    });
} else {
    fullConfig.push(
        merge(common, devOptions)
    );
}

module.exports = fullConfig;
