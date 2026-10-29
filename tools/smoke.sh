#!/usr/bin/env bash
# Smoke test for the local 3Moons instance.
#
# Usage:
#   tools/smoke.sh                 # game pages
#   ADMIN=1 tools/smoke.sh         # game + admin pages
#   BASE_URL=http://localhost:8080 SMOKE_USER=admin SMOKE_PASS=admin123 tools/smoke.sh
#
# Exit codes: 0 = no failures, 1 = failures, 2 = login/setup failure.

set -u

BASE_URL="${BASE_URL:-http://localhost:8080}"
SMOKE_USER="${SMOKE_USER:-admin}"
SMOKE_PASS="${SMOKE_PASS:-admin123}"
ADMIN="${ADMIN:-0}"
TMP="${TMPDIR:-/tmp}/3moons-smoke.$$"
JAR="$TMP/cookies.txt"
mkdir -p "$TMP"

GAME_PAGES=(
  "overview"
  "buildings"
  "research"
  "shipyard"
  "resources"
  "techtree"
  "galaxy&galaxy=1&system=1"
  "fleetTable"
  "messages"
  "alliance"
  "buddyList"
  "notes"
  "statistics"
  "records"
  "battleHall"
  "battleSimulator"
  "information&id=1"
  "phalanx"
  "officier"
  "officier&mode=UpdateOfficier"
  "achievements"
  "academy"
  "achievement"
  "arsenal"
  "upgrades"
  "market"
  "auction"
  "achievements&group=daily"
  "achievements&group=build"
  "achievements&group=tech"
  "achievements&group=fleet"
  "achievements&group=def"
  "achievements&group=varia"
  "trader"
  "fleetDealer"
  "settings"
  "playerCard&id=1"
  "search"
  "questions"
  "ticket"
  "chat"
  "imperium"
  "fleetMissile"
  "information"
  "information&id=999999"
  "playerCard&id=999999"
  "notes&mode=detail&id=999999"
  "buddyList&mode=request&id=999999"
  "raport&mode=battlehall&raport=invalid"
  "alliance&mode=info&id=999999"
  "fleetTable&mode=createACS"
  "messages&mode=view&messcat=100"
  "nonexistent"
)

ADMIN_PAGES=(
  "infos"
  "rights"
  "config"
  "configuni"
  "configmods"
  "chat"
  "teamspeak"
  "facebook"
  "module"
  "statsconf"
  "disclamer"
  "create"
  "accounteditor"
  "active"
  "bans"
  "messagelist"
  "globalmessage"
  "fleets"
  "accountdata"
  "support"
  "password"
  "search"
  "qeditor"
  "statsupdate"
  "news"
  "topnav"
  "overview"
  "menu"
  "clearcache"
  "universe"
  "multiips"
  "log"
  "log&type=player"
  "log&type=planet"
  "log&type=settings"
  "log&type=present"
  "log&type=detail&id=999999"
  "vertify"
  "cronjob"
  "cronjob&action=detail&id=999999"
  "cronjob&action=edit&id=999999"
  "giveaway"
  "autocomplete"
  "reset"
  "dump"
)

ERROR_RE='Fatal error|Uncaught|Parse error|ParseError|Warning:|Deprecated:|SQLSTATE|Unknown column|doesn.t exist|USER ERROR'

fail=0
pass=0

login() {
  curl -sS -c "$JAR" -b "$JAR" -o /dev/null "$BASE_URL/index.php" || return 2
  local out
  out=$(curl -sS -c "$JAR" -b "$JAR" -o /dev/null -w '%{http_code} %{redirect_url}' \
    -X POST -d "uni=1&username=$SMOKE_USER&password=$SMOKE_PASS" \
    "$BASE_URL/index.php?page=login") || return 2
  case "$out" in
    302*game.php*) ;;
    *) echo "login failed: $out"; return 2 ;;
  esac
}

admin_login() {
  local out
  out=$(curl -sS -c "$JAR" -b "$JAR" -o /dev/null -w '%{http_code} %{redirect_url}' \
    -X POST -d "admin_pw=$SMOKE_PASS" "$BASE_URL/admin.php") || return 2
  case "$out" in
    302*admin.php*) ;;
    *) echo "admin login failed: $out"; return 2 ;;
  esac
}

check_page() {
  local label="$1" url="$2" file="$TMP/page.html"
  local code
  code=$(curl -sS -c "$JAR" -b "$JAR" -o "$file" -w '%{http_code}' "$url")
  local errs
  errs=$(grep -oiE "$ERROR_RE" "$file" | sort -u | tr '\n' '|')
  if [ "$code" != "200" ] || [ -n "$errs" ]; then
    printf 'FAIL %-3s %-42s %s\n' "$code" "$label" "$errs"
    fail=$((fail + 1))
  else
    printf 'ok   %-3s %s\n' "$code" "$label"
    pass=$((pass + 1))
  fi
}

login || { echo "cannot log in; is the stack up? ($BASE_URL)"; rm -rf "$TMP"; exit 2; }


echo "== game =="
for p in "${GAME_PAGES[@]}"; do
  check_page "game.php?page=$p" "$BASE_URL/game.php?page=$p"
done

if [ "$ADMIN" = "1" ]; then
  if admin_login; then
    SID=$(awk -F'	' '$6=="2Moons"{print $7}' "$JAR" | tail -1)
    echo "== admin =="
    for p in "${ADMIN_PAGES[@]}"; do
      check_page "admin.php?page=$p" "$BASE_URL/admin.php?page=$p&sid=$SID"
    done
  else
    echo "admin login failed; skipping admin pages"
    fail=$((fail + 1))
  fi
fi

echo
echo "pass=$pass fail=$fail"
rm -rf "$TMP"
[ "$fail" -eq 0 ]
