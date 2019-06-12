const merge = require('webpack-merge');
const common = require('./webpack.common.js');

const TerserPlugin = require('terser-webpack-plugin');
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");

const productionOptions = {
    mode: 'production',
    optimization: {
        minimizer: [
            new TerserPlugin({
                cache: true,
                parallel: true,
                sourceMap: true
            }),
            new OptimizeCSSAssetsPlugin({})
        ]
    }
};

let fullConfig = [];
if (Array.isArray(common)) {
    common.forEach(function (conf) {
        fullConfig.push(
            merge(conf, productionOptions)
        );
    });
} else {
    fullConfig.push(
        merge(common, productionOptions)
    );
}

module.exports = fullConfig;
