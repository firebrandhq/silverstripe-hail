import React from 'react';
import { render } from 'react-dom';
import registerDependencies from './registerDependencies';
import { ApolloProvider } from 'react-apollo';
import Injector, { InjectorProvider, provideInjector, inject } from 'lib/Injector';
import App from './App';

registerDependencies();

Injector.ready(() => {
    const { apolloClient, store } = window.ss;
    const MyApp = () => (
        <ApolloProvider client={apolloClient} store={store}>
            <App />
        </ApolloProvider>
    );
    const MyAppWithInjector = provideInjector(MyApp);

    $('#articles-app').entwine({
        onmatch() {
            render(
                <MyAppWithInjector />,
                this[0]
            )
        }
    })
});
