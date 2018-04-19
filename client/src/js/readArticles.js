import { graphqlTemplates } from 'lib/Injector';

const { READ } = graphqlTemplates;

const query = {
    apolloConfig: {
        props({ data: { readArticles } }) {
            return {
                articles: readArticles,
            }
        }
    },
    templateName: READ,
    pluralName: 'Articles',
    pagination: false,
    params: {},
    fields: [
        'Content',
        'ID'
    ],
};

export default query;