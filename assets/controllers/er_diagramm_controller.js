import { Controller } from '@hotwired/stimulus';
import mermaid from 'mermaid';
/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['container'];

    async connect() {
        const diagramDefinition = `
            erDiagram
                USER ||--o{ ADDRESS : "besitzt"
                USER }o--o{ STATION : "Favoriten"
                STATION ||--o| STATION_DETAIL : "Details"
                STATION ||--o{ PRICE : "Historie"
                STATION_DETAIL ||--o{ OPENING_TIME : "Öffnungszeiten"

                USER {
                    int id PK
                    string email UK
                    string password
                    json roles
                    bool is_verified
                }

                ADDRESS {
                    int id PK
                    int user_id FK
                    string name
                    string street
                    string post_code
                    string city
                    decimal lat
                    decimal lng
                }

                STATION {
                    int id PK
                    string uuid UK
                    string name
                    string brand
                    string street
                    string house_number
                    string post_code
                    string place
                    decimal lat
                    decimal lng
                }

                STATION_DETAIL {
                    int id PK
                    int station_id FK, UK
                    json opening_times
                    json overrides
                    bool whole_day
                    string state
                }

                OPENING_TIME {
                    int id PK
                    int station_detail_id FK
                    string text
                    string start
                    string end
                }

                PRICE {
                    int id PK
                    int station_id FK
                    decimal diesel
                    decimal e5
                    decimal e10
                    datetime created_at
                }
        `;

        mermaid.initialize({
            startOnLoad: false,
            theme: 'neutral',
            themeVariables: {
                fontFamily: '"Inter", ui-sans-serif, system-ui, -apple-system, sans-serif',
                fontSize: '14px',
                primaryColor: '#3b82f6',
                primaryTextColor: '#1e293b',
                primaryBorderColor: '#3b82f6',
                lineColor: '#64748b',
                secondaryColor: '#f8fafc',
                tertiaryColor: '#ffffff',
                mainBkg: '#ffffff',
                nodeBorder: '#cbd5e1',
                clusterBkg: '#f8fafc',
                titleColor: '#0f172a',
                attributeFill: '#475569',
            },
            er: {
                useMaxWidth: true,
                fontSize: 14,
                entityPadding: 20,
                layoutDirection: 'LR',
                minEntityWidth: 140,
                minEntityHeight: 75,
            }
        });

        try {
            const { svg } = await mermaid.render('mermaid-svg', diagramDefinition);
            this.containerTarget.innerHTML = svg;
        } catch (error) {
            console.error('Mermaid rendering failed:', error);
            this.containerTarget.innerHTML = '<p class="text-red-500">Diagramm konnte nicht geladen werden.</p>';
        }
    }
}
