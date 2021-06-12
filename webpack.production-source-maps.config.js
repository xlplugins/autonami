const MiniCSSExtractPlugin = require('mini-css-extract-plugin');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const TerserPlugin = require('terser-webpack-plugin');
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');
const {CleanWebpackPlugin} = require('clean-webpack-plugin');
const path = require('path');
const webpack = require("webpack");

module.exports = {
    // define entry file and output
    mode: 'production',
    entry: {
        main: './admin/frontend/src/index.js',
        public: './public/app/index.js',
    },
    output: {
        path: path.resolve('admin/frontend/dist'),
        filename: '[name].js',
        chunkFilename: '[name].[contenthash].js',
    },
    plugins: [
        new CleanWebpackPlugin(),
        new MiniCSSExtractPlugin({
            filename: '[name].css',
            chunkFilename: '[name].[contenthash].css',
            ignoreOrder: true,
        }),
        new BundleAnalyzerPlugin(),
        new DependencyExtractionWebpackPlugin({injectPolyfill: true}),
        new webpack.DefinePlugin({envMode: 'production'})
    ],
    optimization: {
        minimizer: [
            new TerserPlugin({
                cache: true,
                parallel: true,
                sourceMap: false,
                terserOptions: {
                    output: {
                        comments: /translators:/i,
                    },
                },
                extractComments: false,
            }),
        ],
        splitChunks: {
            automaticNameDelimiter: '--',
            cacheGroups: {
                styles: {
                    name: 'styles',
                    test: /\.css$/,
                    chunks: 'all',
                    enforce: true,
                },
            },
        }
    },
    module: {
        rules: [
            {
                test: /\.jsx?$/,
                loader: 'babel-loader',
                exclude: /node_modules/,
                options: {
                    presets: ['@babel/preset-env']
                }
            },
            {
                test: /\.svg$/i,
				loader: ['@svgr/webpack'],
            },
            {
                test: /\.s?css$/,
                use: [
                    MiniCSSExtractPlugin.loader,
                    'css-loader',
                    {
                        loader: 'sass-loader',
                        options: {
                            sassOptions: {
                                includePaths: [
                                    './admin/frontend/src/assets/css/abstracts'
                                ],
                            },
                            additionalData:
                                '@import "_colors"; ' +
                                '@import "_variables"; ' +
                                '@import "_breakpoints"; ' +
                                '@import "_mixins"; ',
                        },
                    },
                ]
            },
            {
                test: /\.(woff|woff2|eot|ttf|otf)$/,
                use: [
                    'file-loader',
                ],
            },
        ]
    },
    devtool: 'eval-source-map'
};