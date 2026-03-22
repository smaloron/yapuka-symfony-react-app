describe(
    'Vérification de la config',
    () => {
        it('est accessible', () => {
            expect(true).to.be.true;
        });
        it('accède aux variables d\'environnement', () => {
            //expect(cy.env('apiUrl')).to.be.a('string');
            expect(Cypress.env("apiUrl")).to.be.a('string');
            expect(Cypress.env("apiUrl")).to.contain('8080');
            expect(Cypress.env("appName")).to.eq('Yapuka');
        });

        it('Charge la page d\'accueil', () => {
            cy.visit('/');
            cy.contains('Yapuka');
            cy.contains('Créer un compte');
            cy.get('button').should('contain', 'Se connecter');

        })


    });