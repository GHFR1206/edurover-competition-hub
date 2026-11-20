import { useState } from "react";
import { AuthLayout, Footer, Header } from "./components/layout";
import { CheckEmailForm, ForgotPasswordForm, LoginForm, RegisterForm } from "./components/forms";
import { pageContent, type PageName } from "./data/authData";

export default function App() {
  const [page, setPage] = useState<PageName>("login");
  const [resetEmail, setResetEmail] = useState("coach@sekolah.contoh");

  const navigate = (nextPage: PageName) => {
    setPage(nextPage);
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  const content = pageContent[page];

  return (
    <div className={`app ${page === "register" ? "app--register" : ""}`}>
      <Header onNavigate={navigate} />
      <main className="main">
        <img
          className="sky-decoration"
          src={page === "login" || page === "register" ? "/assets/d5b5e.svg" : "/assets/cf19b.svg"}
          alt=""
        />
        <AuthLayout
          content={content}
          register={page === "register"}
          compact={page === "forgot" || page === "check-email"}
        >
          {page === "login" && <LoginForm onNavigate={navigate} />}
          {page === "register" && <RegisterForm onNavigate={navigate} />}
          {page === "forgot" && (
            <ForgotPasswordForm
              onNavigate={navigate}
              onSent={(email) => {
                setResetEmail(email);
                navigate("check-email");
              }}
            />
          )}
          {page === "check-email" && <CheckEmailForm email={resetEmail} onNavigate={navigate} />}
        </AuthLayout>
      </main>
      <Footer register={page === "register"} />
    </div>
  );
}
