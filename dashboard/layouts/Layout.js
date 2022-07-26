import React from "react";
import Sidebar from "./Sidebar";
import Header from "./Header";

const Layout = ({ children }) => (
    <div id="app" className={"fullHeight"}>
        <Sidebar/>
        <main className="main">
            <Header/>
            {children}
        </main>
    </div>
);
export default Layout;
