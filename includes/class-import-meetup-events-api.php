<?php
/**
 * Meetup GraphQL Wrapper.
 *
 * @author     Rajat Patel
 */

/**
 * Meetup GraphQL Wrapper class.
 *
 * @category   Class
 */
class Import_Meetup_Events_API {

    /**
     * Contain Meetup GraphQL URL
     * @access private
     */
    private $api_url = 'https://api.meetup.com/gql';
    
    /**
     * Initialize Meetup GraphQL.
     *
     * @param string $access_token    Acccess Token OR key
     */
    public function __construct(){
        $this->client = new Import_Meetup_Events_HttpClient();
    }

    /**
     * Get Meetup Events.
     *
     * @return array Meetup Events
     */
    public function getEvents( $event_id = 0, $api_key = '' ){         
        $query = <<<'GRAPHQL'
                query ($eventId: ID!) {
                    event(id: $eventId) {
                        id
                        title
                        dateTime
                        endTime
                        description
                        shortDescription
                        recurrenceDescription
                        duration
                        timezone
                        eventUrl
                        status
                        venue{
                            id
                            name
                            address
                            city
                            state
                            country
                            lat
                            lng
                            postalCode
                            zoom
                        }
                        onlineVenue{
                            type
                            url
                        }
                        isOnline
                        imageUrl
                        hosts{
                            id
                            name
                            email
                            lat
                            lon
                            city
                            state
                            country
                        }
                        group{
                            id
                            name
                            description
                            emailListAddress
                            urlname
                            logo{
                                baseUrl
                            }
                        }
                    }
                }
                GRAPHQL;
        $variables = ['eventId' => $event_id];
        return $this->client->graphql_query( $this->api_url, $query, $variables, $api_key );
    }
    
    /**
     * Get Meetup Events By Group ID With pagination
     *
     * @return array Group ID
     */
    public function getGroupEvents( $meetup_group_id = '', $itemsNum = 0, $cursor = "", $api_key = '' ){
        $query = <<<'GRAPHQL'
            query ($urlname: String!, $itemsNum: Int!, $cursor: String) {
                groupByUrlname(urlname: $urlname) {
                    upcomingEvents(input: {first: $itemsNum, after: $cursor}){
                        pageInfo{
                            endCursor
                        }
                        count
                        edges {
                            node {
                                id
                                title
                                dateTime
                                endTime
                                description
                                shortDescription
                                recurrenceDescription
                                duration
                                timezone
                                eventUrl
                                status
                                venue{
                                    id
                                    name
                                    address
                                    city
                                    state
                                    country
                                    lat
                                    lng
                                    postalCode
                                    zoom
                                }
                                onlineVenue{
                                    type
                                    url
                                }
                                isOnline
                                imageUrl
                                hosts{
                                    id
                                    name
                                    email
                                    lat
                                    lon
                                    city
                                    state
                                    country
                                }
                                group{
                                    id
                                    name
                                    description
                                    emailListAddress
                                    urlname
                                    logo{
                                        baseUrl
                                    }
                                }
                            }
                        }
                    }
                }
            }
        GRAPHQL;
        $variables = ['urlname' => $meetup_group_id, 'itemsNum' => $itemsNum, 'cursor'=> $cursor ];
        return $this->client->graphql_query( $this->api_url, $query, $variables, $api_key);
    }

    /**
     * Get Meetup Group Name by Gruop ID
     * 
     * @return array Group ID
     */
    public function getGroupName( $meetup_group_id = '', $api_key = '' ){

        $query = <<<'GRAPHQL'
        query ($urlname: String!) {
            groupByUrlname(urlname: $urlname) {
              upcomingEvents(input: {}) {
                count
              }
              name
              id
            }
          }
        GRAPHQL;
        $variables = ['urlname' => $meetup_group_id];
        return $this->client->graphql_query( $this->api_url, $query, $variables, $api_key);
    }

    /**
     * Get Meetup Authorize User Data
     * 
     * @return array User Token
     */
    public function getAuthUser( $api_key = '' ){

        $query = <<<'GRAPHQL'
            query{
                self{
                    id
                    email
                    name
                }
            }
        GRAPHQL;
        $variables = [];
        return $this->client->graphql_query( $this->api_url, $query, $variables, $api_key);
    }

}
  