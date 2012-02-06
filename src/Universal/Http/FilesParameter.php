<?php 
namespace Universal\Http;

/**
 * $req = new HttpRequest;
 * $req->files->uploaded_file->name;
 * $req->files->uploaded_file->size;
 */
class FilesParameter extends Parameter
{
    public $hash;

    function __construct( $hash )
    {
        $files = array();
        foreach( array_keys($hash) as $name ) {

            // multiple files
            if( is_array($hash[ $name ][ 'error' ]) ) {
                $files[ $name ] = array();
                for( $i = 0 ; $i < count( $hash[ $name ]['error'] ) ; ++$i ) {
                    $attributes = array( 
                        'name' => $hash[$name]['name'][ $i ],
                        'size' => $hash[$name]['size'][ $i ],
                        'type' => $hash[$name]['type'][ $i ],
                        'error' => $hash[$name]['error'][ $i ],
                        'tmp_name' => $hash[$name]['tmp_name'][ $i ],
                    );
                    $files[ $name ][] = new File( $attributes );
                }
            }
            else {
                // single file
                $files[ $name ] = new File( $hash[ $name ] );
            }
        }
        $this->hash = $files;
    }

}