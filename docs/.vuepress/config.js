module.exports = {

    markdown: {
        anchor: { level: [2, 3] },
        extendMarkdown(md) {
            let markup = require("vuepress-theme-craftdocs/markup");
            md.use(markup);
        },
    },

    base: '/spoon/',
    title: 'Spoon plugin for Craft CMS',
    plugins: [
        [
            'vuepress-plugin-clean-urls',
            {
                normalSuffix: '/',
                indexSuffix: '/',
                notFoundPath: '/404.html',
            },
        ],
    ],
    theme: "craftdocs",
    themeConfig: {
        codeLanguages: {
            php: "PHP",
            twig: "Twig",
            html: "HTML",
            js: "JavaScript",
        },
        logo: '/images/icon.svg',
        searchMaxSuggestions: 10,
        nav: [
            {text: 'Getting Started️', link: '/getting-started/'},
            {
                text: 'How It Works',
                items: [
                    {text: 'Matrix Block Groups', link: '/matrix-block-groups/'},
                    {text: 'Matrix Block Tabs', link: '/matrix-block-tabs/'},
                    {text: 'Overrides', link: '/overrides/'},
                ]
            },
            {
                text: 'More',
                items: [
                    {text: 'Double Secret Agency', link: 'https://www.doublesecretagency.com/plugins'},
                    {text: 'Our other Craft plugins', link: 'https://plugins.doublesecretagency.com', target:'_self'},
                ]
            },
        ],
        sidebar: {
            // Getting Started
            '/getting-started/': [
                '',
                'settings',
                'config',
            ],
            // How It Works
            '/': [
                'matrix-block-groups',
                'matrix-block-tabs',
                'overrides',
            ],
        }
    }
};
