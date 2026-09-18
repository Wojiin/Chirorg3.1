import http from "k6/http";
import { check, group, sleep } from "k6";

const apiBaseUrl = (__ENV.API_BASE_URL || "http://127.0.0.1:8080/api").replace(
  /\/$/,
  "",
);
// Avoid the reserved K6_VUS/K6_DURATION names: those variables would replace
// the scenario declared below instead of merely configuring it.
const virtualUsers = Number.parseInt(__ENV.CHIRORG_VUS || "2", 10);
const duration = __ENV.CHIRORG_DURATION || "30s";

export const options = {
  scenarios: {
    consultation: {
      executor: "constant-vus",
      vus: virtualUsers,
      duration,
      gracefulStop: "5s",
    },
  },
  thresholds: {
    checks: ["rate>0.99"],
    http_req_failed: ["rate<0.01"],
    http_req_duration: ["p(95)<800"],
    "http_req_duration{endpoint:programmes}": ["p(95)<1000"],
  },
};

export function setup() {
  const email = __ENV.TEST_EMAIL;
  const password = __ENV.TEST_PASSWORD;
  if (!email || !password) {
    throw new Error("TEST_EMAIL et TEST_PASSWORD sont obligatoires.");
  }

  const response = http.post(
    `${apiBaseUrl}/auth/login`,
    JSON.stringify({ email, password }),
    {
      headers: { "Content-Type": "application/json" },
      tags: { endpoint: "connexion" },
    },
  );
  const authenticated = check(response, {
    "la connexion de charge réussit": (result) => result.status === 200,
    "la connexion retourne un JWT": (result) => Boolean(result.json("token")),
  });
  if (!authenticated) {
    throw new Error(
      `Connexion impossible pendant la préparation k6 (${response.status}).`,
    );
  }

  return { token: response.json("token") };
}

export default function ({ token }) {
  const params = {
    headers: {
      Accept: "application/ld+json, application/json",
      Authorization: `Bearer ${token}`,
    },
  };

  group("consultation des référentiels", () => {
    const specialites = http.get(
      `${apiBaseUrl}/specialites?page=1&itemsPerPage=10`,
      { ...params, tags: { endpoint: "specialites" } },
    );
    check(specialites, {
      "les spécialités répondent en 200": (response) => response.status === 200,
      "les spécialités restent paginées": (response) =>
        Array.isArray(response.json("member")),
    });

    const salles = http.get(`${apiBaseUrl}/salles?page=1&itemsPerPage=10`, {
      ...params,
      tags: { endpoint: "salles" },
    });
    check(salles, {
      "les salles répondent en 200": (response) => response.status === 200,
    });
  });

  group("consultation des programmes", () => {
    const programmes = http.get(
      `${apiBaseUrl}/programmes-operatoires?page=1&itemsPerPage=10`,
      { ...params, tags: { endpoint: "programmes" } },
    );
    check(programmes, {
      "les programmes répondent en 200": (response) => response.status === 200,
      "les programmes retournent une collection": (response) =>
        Array.isArray(response.json("member")),
    });
  });

  sleep(1);
}
