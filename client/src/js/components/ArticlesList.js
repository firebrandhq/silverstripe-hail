import React from 'react';
import { inject } from 'lib/Injector';

const ArticlesList = ({ articles = [], ItemComponent }) => (
    <ul className="articles">
        {articles.map(article => <ItemComponent key={article.ID} article={note} />)}
    </ul>
);

export default inject(
    ['ArticlesListItem'],
    (ArticlesListItem) => ({
        ItemComponent: ArticlesListItem
    })
)(ArticlesList);