#!/usr/bin/env bash
#
# Drive the PHP 8.3 containerized test environment (see compose.yaml).
#
# @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
# @copyright 2016-2026 Marcin Orlowski
# @license   http://www.opensource.org/licenses/mit-license.php MIT
# @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
#
set -euo pipefail

SCRIPT_NAME="$(basename "${BASH_SOURCE[0]}")"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)"
readonly SCRIPT_NAME SCRIPT_DIR

readonly SERVICE="test"

log_error() { printf '%s\n' "ERROR: ${1}" >&2; }

# Resolve the compose command once: prefer the v2 plugin, fall back to legacy.
COMPOSE=""
resolve_compose() {
    command -v docker >/dev/null 2>&1 || {
        log_error "docker not found on PATH."
        exit 1
    }
    if docker compose version >/dev/null 2>&1; then
        COMPOSE="docker compose"
    elif command -v docker-compose >/dev/null 2>&1; then
        COMPOSE="docker-compose"
    else
        log_error "Neither 'docker compose' nor 'docker-compose' is available."
        exit 1
    fi
    readonly COMPOSE
}

# Run a one-off command in a fresh, auto-removed container.
compose_run() {
    # word-splitting COMPOSE is intentional ("docker compose" is two words).
    # shellcheck disable=SC2086
    ${COMPOSE} run --rm "${SERVICE}" "$@"
}

show_usage() {
    cat <<EOF
Usage: ${SCRIPT_NAME} [COMMAND] [ARGS...]

Runs the project test tooling inside a PHP 8.3 container.
Runs this help when no command is given.

Commands:
  test            Run the full PHPUnit suite
  filter <name>   Run a single test / method by name
  lint            Run PHPStan (level max)
  phpcs           Run PHP_CodeSniffer
  mdlint          Run markdownlint
  shell           Open an interactive bash shell in the container
  build           Build (or rebuild) the image
  clean           Remove the image and the vendor volume
  <anything else> Passed through verbatim to the container

  -h, --help      Show this help

Examples:
  ${SCRIPT_NAME} test
  ${SCRIPT_NAME} filter testSuccessResponse
  ${SCRIPT_NAME} composer show
EOF
}

main() {
    resolve_compose

    local cmd="${1:-help}"
    if [[ "$#" -gt 0 ]]; then
        shift
    fi

    case "${cmd}" in
        -h | --help | help)
            show_usage
            ;;
        test)
            compose_run composer test
            ;;
        filter)
            if [[ "$#" -lt 1 ]]; then
                log_error "'filter' needs a test name. See '${SCRIPT_NAME} -h'."
                exit 2
            fi
            compose_run vendor/bin/phpunit -c tests/phpunit.xml --filter "${1}"
            ;;
        lint)
            compose_run composer lint
            ;;
        phpcs)
            compose_run vendor/bin/phpcs
            ;;
        mdlint)
            compose_run composer mdlint
            ;;
        shell | bash)
            compose_run bash
            ;;
        build)
            # shellcheck disable=SC2086
            ${COMPOSE} build "$@"
            ;;
        clean)
            # shellcheck disable=SC2086
            ${COMPOSE} down --volumes --rmi local --remove-orphans
            ;;
        *)
            compose_run "${cmd}" "$@"
            ;;
    esac
}

# Run from the repo root so compose.yaml is always found.
cd "${SCRIPT_DIR}"
main "$@"
