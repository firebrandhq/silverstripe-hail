import React from 'react';
import { inject } from 'lib/Injector';

const App = ({ ListComponent }) => (
    <div>
        <h3>Articles</h3>
        <ListComponent />
    </div>
);

export default inject(
    ['ArticlesList'],
    (ArticlesList) => ({
        ListComponent: ArticlesList,
    })
)(App);