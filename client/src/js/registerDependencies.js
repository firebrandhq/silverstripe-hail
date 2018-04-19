import ArticlesList from './components/ArticlesList';
import ArticlesListItem from './components/ArticlesListItem';
import readArticles from './readArticles';
import Injector, { injectGraphql } from 'lib/Injector';

const registerDependencies = () => {
    Injector.component.register('ArticlesList', ArticlesList);
    Injector.component.register('ArticlesListItem', ArticlesListItem);
    Injector.query.register('ReadArticles', readArticles);
    Injector.transform(
        'articleslist-graphql',
        (updater) => {
            updater.component('ArticlesList', injectGraphql('ArticlesNotes'));
        }
    );
};

export default registerDependencies;