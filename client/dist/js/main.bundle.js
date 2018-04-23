/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// identity function for calling harmony imports with the correct context
/******/ 	__webpack_require__.i = function(value) { return value; };
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./client/src/js/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./client/src/js/App.js":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_react__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_lib_Injector__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_lib_Injector___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_lib_Injector__);



var App = function App(_ref) {
    var ListComponent = _ref.ListComponent;
    return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
        'div',
        null,
        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
            'h3',
            null,
            'Articles'
        ),
        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(ListComponent, null)
    );
};

/* harmony default export */ __webpack_exports__["a"] = (__webpack_require__.i(__WEBPACK_IMPORTED_MODULE_1_lib_Injector__["inject"])(['ArticlesList'], function (ArticlesList) {
    return {
        ListComponent: ArticlesList
    };
})(App));

/***/ }),

/***/ "./client/src/js/components/ArticlesList.js":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_react__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_lib_Injector__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_lib_Injector___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_lib_Injector__);



var ArticlesList = function ArticlesList(_ref) {
    var _ref$articles = _ref.articles,
        articles = _ref$articles === undefined ? [] : _ref$articles,
        ItemComponent = _ref.ItemComponent;
    return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
        'ul',
        { className: 'articles' },
        articles.map(function (article) {
            return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(ItemComponent, { key: article.ID, article: note });
        })
    );
};

/* harmony default export */ __webpack_exports__["a"] = (__webpack_require__.i(__WEBPACK_IMPORTED_MODULE_1_lib_Injector__["inject"])(['ArticlesListItem'], function (ArticlesListItem) {
    return {
        ItemComponent: ArticlesListItem
    };
})(ArticlesList));

/***/ }),

/***/ "./client/src/js/components/ArticlesListItem.js":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_react__);


var ArticlesListItem = function ArticlesListItem(_ref) {
  var article = _ref.article;
  return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
    'li',
    null,
    article.Content
  );
};

/* harmony default export */ __webpack_exports__["a"] = (ArticlesListItem);

/***/ }),

/***/ "./client/src/js/index.js":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* WEBPACK VAR INJECTION */(function($) {/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_react__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_react_dom__ = __webpack_require__(2);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_react_dom___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_react_dom__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__registerDependencies__ = __webpack_require__("./client/src/js/registerDependencies.js");
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_react_apollo__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_react_apollo___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_3_react_apollo__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_lib_Injector__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_lib_Injector___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_4_lib_Injector__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__App__ = __webpack_require__("./client/src/js/App.js");







__webpack_require__.i(__WEBPACK_IMPORTED_MODULE_2__registerDependencies__["a" /* default */])();

__WEBPACK_IMPORTED_MODULE_4_lib_Injector___default.a.ready(function () {
    var _window$ss = window.ss,
        apolloClient = _window$ss.apolloClient,
        store = _window$ss.store;

    var MyApp = function MyApp() {
        return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
            __WEBPACK_IMPORTED_MODULE_3_react_apollo__["ApolloProvider"],
            { client: apolloClient, store: store },
            __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(__WEBPACK_IMPORTED_MODULE_5__App__["a" /* default */], null)
        );
    };
    var MyAppWithInjector = __webpack_require__.i(__WEBPACK_IMPORTED_MODULE_4_lib_Injector__["provideInjector"])(MyApp);

    $('#articles-app').entwine({
        onmatch: function onmatch() {
            __webpack_require__.i(__WEBPACK_IMPORTED_MODULE_1_react_dom__["render"])(__WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(MyAppWithInjector, null), this[0]);
        }
    });
});
/* WEBPACK VAR INJECTION */}.call(__webpack_exports__, __webpack_require__(4)))

/***/ }),

/***/ "./client/src/js/readArticles.js":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_lib_Injector__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_lib_Injector___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_lib_Injector__);


var READ = __WEBPACK_IMPORTED_MODULE_0_lib_Injector__["graphqlTemplates"].READ;


var query = {
    apolloConfig: {
        props: function props(_ref) {
            var readArticles = _ref.data.readArticles;

            return {
                articles: readArticles
            };
        }
    },
    templateName: READ,
    pluralName: 'Articles',
    pagination: false,
    params: {},
    fields: ['Content', 'ID']
};

/* harmony default export */ __webpack_exports__["a"] = (query);

/***/ }),

/***/ "./client/src/js/registerDependencies.js":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__components_ArticlesList__ = __webpack_require__("./client/src/js/components/ArticlesList.js");
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__components_ArticlesListItem__ = __webpack_require__("./client/src/js/components/ArticlesListItem.js");
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__readArticles__ = __webpack_require__("./client/src/js/readArticles.js");
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_lib_Injector__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_lib_Injector___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_3_lib_Injector__);





var registerDependencies = function registerDependencies() {
    __WEBPACK_IMPORTED_MODULE_3_lib_Injector___default.a.component.register('ArticlesList', __WEBPACK_IMPORTED_MODULE_0__components_ArticlesList__["a" /* default */]);
    __WEBPACK_IMPORTED_MODULE_3_lib_Injector___default.a.component.register('ArticlesListItem', __WEBPACK_IMPORTED_MODULE_1__components_ArticlesListItem__["a" /* default */]);
    __WEBPACK_IMPORTED_MODULE_3_lib_Injector___default.a.query.register('ReadArticles', __WEBPACK_IMPORTED_MODULE_2__readArticles__["a" /* default */]);
    __WEBPACK_IMPORTED_MODULE_3_lib_Injector___default.a.transform('articleslist-graphql', function (updater) {
        updater.component('ArticlesList', __webpack_require__.i(__WEBPACK_IMPORTED_MODULE_3_lib_Injector__["injectGraphql"])('ArticlesNotes'));
    });
};

/* harmony default export */ __webpack_exports__["a"] = (registerDependencies);

/***/ }),

/***/ 0:
/***/ (function(module, exports) {

module.exports = Injector;

/***/ }),

/***/ 1:
/***/ (function(module, exports) {

module.exports = React;

/***/ }),

/***/ 2:
/***/ (function(module, exports) {

module.exports = ReactDom;

/***/ }),

/***/ 3:
/***/ (function(module, exports) {

module.exports = ReactApollo;

/***/ }),

/***/ 4:
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ })

/******/ });
//# sourceMappingURL=main.bundle.js.map