$data = array(
    's' => 'b',
    'c' => 'd'
);
$query = http_build_query($data);
// var_dump($query);
var_dump(betterParseStr($query));
function betterParseStr( $string )
{
    return array_reduce( explode( "&", $string ), function( $array, $string_piece ) {
        if( $string_piece === "" ) return $array;
        $equal_offset = strpos( $string_piece, "=" );
        if( $equal_offset === FALSE ) {
            $key = urldecode( $string_piece );
            $value = "";
        } else {
            $key = urldecode( substr( $string_piece, 0, $equal_offset ) );
            $value = urldecode( substr( $string_piece, $equal_offset + 1 ) );
        }
        if( preg_match( "/^([^\[]*)\[([^\]]*)](.*)$/", $key, $matches ) ) {
            $key_path = array( $matches[1], $matches[2] );
            $rest = $matches[3];
            while( preg_match( "/^\[([^\]]*)](.*)$/", $rest, $matches ) ) {
                $key_path[] = $matches[1];
                $rest = $matches[2];
            }
        } else {
            //replace first [ for _
            //why?!? idk ask PHP it does
            //Example: ?key[[=value -> array( "key_[" => "value" )
            $key_path = array( preg_replace('/\[/', '_', $key, 1 ) );
        }
        if( strlen( $key_path[0] ) > 0 && substr( $key_path[0], 0, 1 ) !== "[" ) {
            $current_node = &$array;
            $last_key = array_pop( $key_path );
            $resolve_key = function( $key, array $array ) {
                if( $key === "" || $key === " " ) {
                    $int_array = array_filter( array_keys( $array ), function( $key ) { return is_int( $key ); } );
                    $key = $int_array ? max( $int_array ) + 1 : 0;
                }
                return $key;
            };
            foreach( $key_path as $key_path_piece ) {
                $key_path_piece = $resolve_key( $key_path_piece, $current_node );
                if( ! array_key_exists( $key_path_piece, $current_node ) || ! is_array( $current_node[$key_path_piece] ) ) {
                    $current_node[$key_path_piece] = array();
                }
                $current_node = &$current_node[$key_path_piece];
            }
            $current_node[$resolve_key( $last_key, $current_node )] = $value;
        }
        return $array;
    }, array() );
}
