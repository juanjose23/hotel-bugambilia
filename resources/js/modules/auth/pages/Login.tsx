import LoginForm from '@/modules/auth/components/LoginForm';
import LayoutPublico from '@/modules/shared/layouts/LayoutPublico';

export default function Login() {
    return (
        <LayoutPublico>
            <LoginForm />
        </LayoutPublico>
    );
}
