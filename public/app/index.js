import {render} from '@wordpress/element';
import {Button} from "@wordpress/components";
import '@wordpress/components/build-style/style.css';

const App = () => {
    return (
        <>
            <Button isPrimary onClick={() => console.log('Hello World')}>Hello World</Button>
        </>
    );
}

render(<App/>, document.getElementById('bwfcrm-public'));